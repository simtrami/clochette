<?php

namespace App\Controller;

use Algolia\SearchBundle\SearchService;
use App\Entity\Account;
use App\Entity\DetailsTransactions;
use App\Entity\StockMarketData;
use App\Entity\Stocks;
use App\Entity\Transactions;
use App\Entity\TypeStocks;
use App\Entity\Users;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PurchaseController extends BasicController
{
    protected $searchService, $algoliaAppId, $algoliaApiSearchKey, $algoliaIndex;
    protected $escposPrinterIP, $escposPrinterPort;
    protected $security;

    public function __construct(Security $security)
    {
        $this->algoliaAppId = getenv('ALGOLIA_APP_ID');
        $this->algoliaApiSearchKey = getenv('ALGOLIA_API_SEARCH_KEY');
        $this->algoliaIndex = getenv('ALGOLIA_INDEX');
        $this->searchService = SearchService::class;
        $this->escposPrinterIP = getenv('ESCPOS_PRINTER_IP');
        $this->escposPrinterPort = getenv('ESCPOS_PRINTER_PORT');
        $this->security = $security;
    }

    /**
     * @Route("/purchase", name="purchase")
     **/
    public function showIndex(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $repo_stocks = $this->getDoctrine()->getRepository(Stocks::class);
        $repo_typeStocks = $this->getDoctrine()->getRepository(TypeStocks::class);

        if (in_array("stockmarket", $this->data['activeModes'], true)) {
            $this->data['smd_articles'] = $this->getDoctrine()->getRepository(StockMarketData::class)->findAll();
        } else {
            // Réduction sur le cidre
            $time = date('H:m');
            $cider = $repo_stocks->findOneBy(['name' => 'Cidre']);
            if ($cider) {
                $em = $this->getDoctrine()->getManager();
                if ($time >= '21:50' && $time <= '23:00' && $cider->getSellingPrice() === 2.5) {
                    $cider->setSellingPrice(2);
                    $em->persist($cider);
                    $em->flush();
                } elseif (!($time >= '21:50' && $time <= '23:00') && $cider->getSellingPrice() === 2) {
                    $cider->setSellingPrice(2.5);
                    $em->persist($cider);
                    $em->flush();
                }
            }
        }

        try {
            $draft = $repo_typeStocks->returnType('Fût');
        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Not type named 'Fût'");
        }
        $selected_drafts = $repo_stocks->loadStocksForSaleByType($draft);
        try {
            $bottle = $repo_typeStocks->returnType('Bouteille');
        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Not type named 'Bouteille'");
        }
        $selected_bottles = $repo_stocks->loadStocksForSaleByType($bottle);
        try {
            $article = $repo_typeStocks->returnType('Nourriture ou autre');
        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Not type named 'Nourriture ou autre'");
        }
        $selected_articles = $repo_stocks->loadStocksForSaleByType($article);

        $this->data['selected_drafts'] = $selected_drafts;
        $this->data['selected_bottles'] = $selected_bottles;
        $this->data['selected_articles'] = $selected_articles;

        $this->data['algoliaAppId'] = $this->algoliaAppId;
        $this->data['algoliaApiSearchKey'] = $this->algoliaApiSearchKey;
        $this->data['algoliaIndex'] = $this->algoliaIndex;

        return $this->render("purchase/index.html.twig", $this->data);
    }

    /**
     * @Route("/purchase/validation", name="purchaseValidation")
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function validateTransaction(Request $request): RedirectResponse
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_INTRO')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();

        if (in_array("stockmarket", $this->data['activeModes'], true)) {
            $repo_smd = $this->getDoctrine()->getRepository(StockMarketData::class);
        }

        $repo_stocks = $this->getDoctrine()->getRepository(Stocks::class);
        $repo_users = $this->getDoctrine()->getRepository(Users::class);
        $repo_account = $this->getDoctrine()->getRepository(Account::class);

        $transaction = new Transactions();

        // Insertion du timestamp dans l'entité Transactions
        $timestamp = date_create(date("Y-m-d H:i:s"));
        $transaction->setTimestamp($timestamp);

        $form = [];
        $form['userId'] = $request->request->get('userId');
        $form['methode'] = $request->request->get('methode');
        $form['drafts'] = $request->request->get('drafts') ?: [];
        $form['bottles'] = $request->request->get('bottles') ?: [];
        $form['articles'] = $request->request->get('articles') ?: [];
        $form['account'] = $request->request->get('accountPseudo');
        $form['withdrawReason'] = $request->request->get('withdrawReason');
        $form['total'] = $request->request->get('total');

        // Insertion de la méthode de paiement dans l'entité Transactions
        $transaction->setMethod($form['methode']);

        // Insertions des articles et de leur quantité (si non nulle) dans des entités DetailsTransactions
        // et calul du montant de la commande (car possibilité d'injecter une fausse valeur en html)
        $amount = 0;

        $em = $this->getDoctrine()->getManager();
        if ($form['withdrawReason'] == 1 && $this->security->isGranted('ROLE_INTRO')) {
            $ecocup = $repo_stocks->findOneBy(['name' => 'Ecocup']);
            if ($ecocup) {
                $transaction->setType(2);
                $detail = new DetailsTransactions();
                $amount = -(abs($form['total']));

                $detail->setArticle($ecocup);
                $detail->setQuantity(abs($form['total']));
                $detail->setTransaction($transaction);
                $ecocup->setQuantity($ecocup->getQuantity() + (abs($form['total'])));

                $em->persist($detail);
                $em->persist($ecocup);
            } else {
                $this->addFlash('error', "There is no article named 'Ecocup'");
            }
        } elseif ($form['withdrawReason'] == 2 && $this->security->isGranted('ROLE_BUREAU')) {
            $transaction->setType(2);

            $amount = -(abs($form['total']));
        } elseif ($form['withdrawReason'] == 0) {
            $transaction->setType(1);

            foreach ($form['drafts'] as $item) {
                if ($item['quantite'] != 0) {
                    $article = $repo_stocks->find($item['id']);

                    if ($article) {
                        $detail = new DetailsTransactions();
                        if (in_array("stockmarket", $this->data['activeModes'], true)) {
                            $amount += $item['quantite'] * $article->getData()->getStockValue();
                        } else {
                            $amount += $item['quantite'] * $article->getSellingPrice();
                        }

                        $detail->setArticle($article);
                        $detail->setQuantity($item['quantite']);
                        $detail->setTransaction($transaction);

                        $em->persist($detail);
                    }
                }
            }
            foreach ($form['bottles'] as $item) {
                if ($item['quantite'] != 0) {
                    $article = $repo_stocks->find($item['id']);

                    if ($article) {
                        $detail = new DetailsTransactions();
                        if (in_array("stockmarket", $this->data['activeModes'], true)) {
                            $amount += $item['quantite'] * $article->getData()->getStockValue();
                        } else {
                            $amount += $item['quantite'] * $article->getSellingPrice();
                        }

                        $detail->setArticle($article);
                        $detail->setQuantity($item['quantite']);
                        $detail->setTransaction($transaction);
                        $article->setQuantity($article->getQuantity() - $item['quantite']);

                        if ($article->getQuantity() == 0) {
                            $article->setIsForSale(false);
                        }

                        $em->persist($detail);
                        $em->persist($article);
                    }
                }
            }
            foreach ($form['articles'] as $item) {
                if ($item['quantite'] != 0) {
                    $article = $repo_stocks->find($item['id']);

                    if ($article) {
                        $detail = new DetailsTransactions();
                        $amount += $item['quantite'] * $article->getSellingPrice();

                        $detail->setArticle($article);
                        $detail->setQuantity($item['quantite']);
                        $detail->setTransaction($transaction);
                        $article->setQuantity($article->getQuantity() - $item['quantite']);

                        if ($article->getQuantity() == 0) {
                            $article->setIsForSale(false);
                        }

                        $em->persist($detail);
                        $em->persist($article);
                    }
                }
            }
        } else {
            $this->addFlash(
                'error',
                "L'utilisateur" . $this->getUser()->getUsername() . " n'a pas le droit d'effectuer cette transaction !"
            );
            return $this->redirectToRoute('purchase');
        }

        if (($amount < 0 && $form['withdrawReason'] == 0) || $amount == 0) {
            $this->addFlash(
                'error',
                "Le montant de la commande semble incorrect, merci de la renvoyer."
            );
            return $this->redirectToRoute('purchase');
        }

        // Insertion du prix dans l'entité Transactions
        $transaction->setAmount($amount);

        $user = $repo_users->find($form['userId']);

        // Paiement par compte : Validation de la commande en fonction de l'utilisateur et du solde du compte
        if ($form['methode'] === "account") {
            $account = $repo_account->findOneBy(['pseudo' => $form['account']]);

            if ($account) {
                $balance = (float)$account->getBalance();

                if (($balance - $amount < 0) && !$this->security->isGranted('ROLE_BUREAU')) {

                    $this->addFlash(
                        'error',
                        "Le solde du compte de " . $account->getFirstName() . " " . $account->getLastName() . " est insuffisant pour valider la commande : Il manque " . (-$balance + $amount) . "€."
                    );
                    return $this->redirectToRoute('purchase');
                }

                $newBalance = $balance - $amount;
                // Insertion du compte dans l'entité Transactions
                $transaction->setAccount($account);
                // Modification du solde du compte
                $account->setBalance($newBalance);
                // Mise à jour du compte dans la base
                $em->persist($account);
            }
        } elseif ($form['methode'] === "cash") { // Paiement par cash : Contrôle de la caisse
            if (getenv('NO_PRINTER')) {
                $this->addFlash('info', 'The printer is disabled.');
            } else {
                $connector = new NetworkPrintConnector($this->escposPrinterIP, $this->escposPrinterPort);
                $printer = new Printer($connector);
                try {
                    $printer->pulse();
                } finally {
                    $printer->close();
                }
            }
        }

        // Insertion de l'user ayant validé la commande dans l'entité Transactions
        $transaction->setStaff($user);

        // Insertion de la commande dans la base
        $em->persist($transaction);

        // Envoie de flashbags
        switch ($form['methode']) {
            case 'account':
                if ($form['withdrawReason'] == '0') {
                    $this->addFlash(
                        'info',
                        "{$transaction->getAmount()}€ ont été débités du compte de {$account->getFirstName()} {$account->getLastName()}."
                    );
                } elseif (!$this->security->isGranted('ROLE_BUREAU')) {
                    $this->addFlash(
                        'error',
                        "Cette fonctionnalité n'est pas autorisée pour cet utilisateur"
                    );
                    return $this->redirectToRoute('purchase');
                } elseif ($form['withdrawReason'] == "1") {
                    $this->addFlash(
                        'info',
                        -$transaction->getAmount() . "€ ont été ajoutés au compte de " . $account->getFirstName() . " " . $account->getLastName() . " pour le retour de " . -$transaction->getAmount() . " écocup(s)."
                    );
                } elseif ($form['withdrawReason'] == "2") {
                    $this->addFlash(
                        'info',
                        -$transaction->getAmount() . "€ ont été remboursés sur le compte de " . $account->getFirstName() . " " . $account->getLastName() . "."
                    );
                } else {
                    $this->addFlash(
                        'error',
                        "Unknown withdraw reason."
                    );
                }
                break;
            case 'cash':
                if ($form['withdrawReason'] == "1" && $this->security->isGranted('ROLE_INTRO')) {
                    $this->addFlash(
                        'info',
                        -$transaction->getAmount() . "€ ont été retirés de la caisse pour le retour de " . -$transaction->getAmount() . " écocup(s)."
                    );
                } elseif ($form['withdrawReason'] == "2" && $this->security->isGranted('ROLE_BUREAU')) {
                    $this->addFlash(
                        'info',
                        -$transaction->getAmount() . "€ ont été retirés de la caisse pour un remboursement."
                    );
                } elseif ($form['withdrawReason'] != "0") {
                    $this->addFlash(
                        'error',
                        "Cette fonctionnalité n'est pas autorisée pour cet utilisateur !"
                    );
                    return $this->redirectToRoute('purchase');
                } else {
                    $this->addFlash(
                        'info',
                        $transaction->getAmount() . "€ ont bien été ajoutés à la caisse."
                    );
                }
                break;
            case 'pumpkin':
                $this->addFlash(
                    'info',
                    $transaction->getAmount() . "€ ont été encaissés par Pumpkin."
                );
                break;
            case 'card':
                $this->addFlash(
                    'info',
                    $transaction->getAmount() . "€ ont été encaissés par carte."
                );
                break;
            default:
                $this->addFlash(
                    'error',
                    "Unknown payment method."
                );
                return $this->redirectToRoute('purchase');
        }

        $em->flush();

//        if (isset($account)) {
//            $this->searchService->index($account, $em);
//        }

        return $this->redirectToRoute('purchase');
    }

    /**
     * @Route("/purchase/open", name="openCashier")
     * @return RedirectResponse
     * @throws Exception
     */
    public function openCashier(): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (getenv('NO_PRINTER')) {
            $this->addFlash('info', 'The printer is disabled.');
            return $this->redirectToRoute('purchase');
        }
        $connector = new NetworkPrintConnector($this->escposPrinterIP, $this->escposPrinterPort);
        $printer = new Printer($connector);
        try {
            $printer->pulse();
            $this->addFlash(
                'info',
                "L'ouverture de la caisse a été effectuée."
            );
        } finally {
            $printer->close();
        }

        return $this->redirectToRoute("purchase");
    }
}
