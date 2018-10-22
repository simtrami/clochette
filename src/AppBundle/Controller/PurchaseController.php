<?php
// src/AppBundle/Controller/PurchaseController.php
namespace AppBundle\Controller;

use Algolia\SearchBundle\IndexManagerInterface;
use AppBundle\Entity\Transactions;
use AppBundle\Entity\DetailsTransactions;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class PurchaseController extends Controller
{
    protected $indexManager;

    /*
     * @var string
     */
    protected $algoliaAppId;

    /*
     * @var string
     */
    protected $algoliaApiSearchKey;

    /*
     * @var string
     */
    protected $algoliaIndex;

    /*
     * @var string
     */
    protected $escposPrinterIP;

    /*
     * @var int
     */
    protected $escposPrinterPort;

    protected $security;


    public function __construct($algoliaAppId, $algoliaApiSearchKey, $algoliaIndex, IndexManagerInterface $indexingManager, $escposPrinterIP, $escposPrinterPort, Security $security)
    {
        $this->algoliaAppId = $algoliaAppId;
        $this->algoliaApiSearchKey = $algoliaApiSearchKey;
        $this->algoliaIndex = $algoliaIndex;
        $this->indexManager = $indexingManager;
        $this->escposPrinterIP = $escposPrinterIP;
        $this->escposPrinterPort = $escposPrinterPort;
        $this->security = $security;
    }

    /**
     * @Route("/purchase", name="purchase")
     **/
    public function showIndex()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');

        // Réduction sur le cidre
	$heure = date('H');
        $cidre = $repo_stocks->findOneBy(['nom' => 'Cidre']);
        $em = $this->getDoctrine()->getManager();
        if ($heure == 22 && $cidre->getPrixVente() == 2.5) {
            $cidre->setPrixVente(2);
            $em->persist($cidre);
            $em->flush();
        } elseif ($heure != 22 && $cidre->getPrixVente() == 2) {
            $cidre->setPrixVente(2.5);
            $em->persist($cidre);
            $em->flush();
        } elseif (!(($heure == 22 && $cidre->getPrixVente() == 2) or ($heure != 22 && $cidre->getPrixVente() == 2.5))) {
            $this->addFlash('erreur', "Un problème concernant le Cidre est survenu !");
        }

        $draft = $repo_typeStocks->returnType('Fût');
        $bottle = $repo_typeStocks->returnType('Bouteille');
        $article = $repo_typeStocks->returnType('Nourriture ou autre');

        /* futs */ $selected_drafts = $repo_stocks->loadStocksForSaleByType($draft);

        /* bouteilles */ $selected_bottles = $repo_stocks->loadStocksForSaleByType($bottle);

        /* articles */ $selected_articles = $repo_stocks->loadStocksForSaleByType($article);

        $data=[];
        $data['selected_drafts'] = $selected_drafts;
        $data['selected_bottles'] = $selected_bottles;
        $data['selected_articles'] = $selected_articles;

        $data['algoliaAppId'] = $this->algoliaAppId;
        $data['algoliaApiSearchKey'] = $this->algoliaApiSearchKey;
        $data['algoliaIndex'] = $this->algoliaIndex;

        return $this->render("purchase/index.html.twig", $data);
    }

    /**
     * @Route("/purchase/validation", name="purchaseValidation")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function validateTransaction(Request $request){
      
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_INTRO')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_users = $this->getDoctrine()->getRepository('AppBundle:Users');
        $repo_account = $this->getDoctrine()->getRepository('AppBundle:Account');

        $commande = new Transactions();

        // Insertion du timestamp dans l'entité Transactions
        $timestamp = date_create(date("Y-m-d H:i:s"));
        $commande->setTimestamp($timestamp);

        $form = [];
        $form['userId'] = $request->request->get('userId');
        $form['methode'] = $request->request->get('methode');
        $form['drafts'] = $request->request->get('drafts');
        $form['bottles'] = $request->request->get('bottles');
        $form['articles'] = $request->request->get('articles');
        $form['account'] = $request->request->get('accountPseudo');
        $form['withdrawReason'] = $request->request->get('withdrawReason');
        $form['total'] = $request->request->get('total');

        // Insertion de la méthode de paiement dans l'entité Transactions
        $commande->setMethode($form['methode']);
      
        // Insertions des articles et de leur quantité (si non nulle) dans des entités DetailsTransactions
        // et calul du montant de la commande (car possibilité d'injecter une fausse valeur en html)
        $montant = 0;

        if ($form['withdrawReason'] == 1 && $this->security->isGranted('ROLE_INTRO')) {
            $commande->setType(2);

            $detail = new DetailsTransactions();
            $article = $repo_stocks->findOneBy(array('nom' => 'Ecocup'));

            $montant = -$form['total'];

            $detail->setArticle($article);
            $detail->setQuantite(-$form['total']);
            $detail->setTransaction($commande);
            $article->setQuantite($article->getQuantite() - $form['total']);

            $em->persist($detail);
            $em->persist($article);
        } elseif ($form['withdrawReason'] == 2 && $this->security->isGranted('ROLE_BUREAU')) {
            $commande->setType(2);

            $montant = -$form['total'];
        } elseif ($form['withdrawReason'] == 0) {
            $commande->setType(1);

            foreach ($form['drafts'] as $item) {
                if ($item['quantite'] != 0) {
                    $detail = new DetailsTransactions();
                    $article = $repo_stocks->find($item['id']);

                    $montant += $item['quantite'] * $article->getPrixVente();

                    $detail->setArticle($article);
                    $detail->setQuantite($item['quantite']);
                    $detail->setTransaction($commande);

                    $em->persist($detail);
                }
            }
            foreach ($form['bottles'] as $item) {
                if ($item['quantite'] != 0) {
                    $detail = new DetailsTransactions();
                    $article = $repo_stocks->find($item['id']);

                    $montant += $item['quantite'] * $article->getPrixVente();

                    $detail->setArticle($article);
                    $detail->setQuantite($item['quantite']);
                    $detail->setTransaction($commande);
                    $article->setQuantite($article->getQuantite() - $item['quantite']);

                    if ($article->getQuantite() == 0){
                        $article->setIsForSale(false);
                    }

                    $em->persist($detail);
                    $em->persist($article);
                }
            }
            foreach ($form['articles'] as $item) {
                if ($item['quantite'] != 0) {
                    $detail = new DetailsTransactions();
                    $article = $repo_stocks->find($item['id']);

                    $montant += $item['quantite'] * $article->getPrixVente();

                    $detail->setArticle($article);
                    $detail->setQuantite($item['quantite']);
                    $detail->setTransaction($commande);
                    $article->setQuantite($article->getQuantite() - $item['quantite']);

                    if ($article->getQuantite() == 0){
                        $article->setIsForSale(false);
                    }

                    $em->persist($detail);
                    $em->persist($article);
                }
            }
        } else {
            $this->addFlash(
                'erreur',
                "L'utilisateur" . $this->getUser()->getUsername() . " n'a pas le droit d'effectuer cette transaction !"
            );
            return $this->redirectToRoute('purchase');
        }
      
        if (($montant < 0 && $form['withdrawReason'] == 0) || $montant == 0) {
            $this->addFlash(
                'erreur',
                "Le montant de la commande semble incorrecte, merci de la renvoyer."
            );
            return $this->redirectToRoute('purchase');
        } else {
            // Insertion du prix dans l'entité Transactions
            $commande->setMontant($montant);
        }
      
        $user = $repo_users->find($form['userId']);
      
        // Paiement par compte : Validation de la commande en fonction de l'utilisateur et du solde du compte
        if ($form['methode'] == "account") {
            $account = $repo_account->findOneBy(['pseudo' => $form['account']]);
            $balance = floatval($account->getBalance());

            if (!$this->security->isGranted('ROLE_BUREAU') && ($balance - $montant < 0)) {

                $this->addFlash(
                    'erreur',
                    "Le solde du compte de " . $account->getFirstName() . " " . $account->getLastName() . " est insuffisant pour valider la commande : Il manque " . (-$balance + $montant) . "€."
                );
                return $this->redirectToRoute('purchase');            
            } else {
                $newBalance = $balance - $montant;
            }
            // Insertion du compte dans l'entité Transactions
            $commande->setAccount($account);
          
            // Modification du solde du compte
            $account->setBalance($newBalance);
            // Mise à jour du compte dans la base
            $em->persist($account);
        }
        // Paiement par cash : Contrôle de la caisse
        elseif ($form['methode'] == "cash") {
            $connector = new NetworkPrintConnector($this->escposPrinterIP, $this->escposPrinterPort);
            $printer = new Printer($connector);
            try {
                $printer->pulse();
            } finally {
                $printer->close();
            }
        }
        
        // Insertion de l'user ayant validé la commande dans l'entité Transactions
        $commande->setUser($user);
      
        // Insertion de la commande dans la base
        $em->persist($commande);
        
        // Envoie de flashbags
        switch ($form['methode']) {
            case 'account':
                if ($form['withdrawReason'] == "1" && $this->security->isGranted('ROLE_BUREAU')) {
                    $this->addFlash(
                        'info',
                        $commande->getMontant() . "€ ont été ajoutés au compte de ".$account->getFirstName()." ".$account->getLastName()." pour le retour de.". $commande->getMontant() ." écocup(s)."
                    );
                } elseif ($form['withdrawReason'] == "2" && $this->security->isGranted('ROLE_BUREAU')) {
                    $this->addFlash(
                        'info',
                        $commande->getMontant() . "€ ont été remboursés sur le compte de ".$account->getFirstName()." ".$account->getLastName()."."
                    );
                } elseif ($form['withdrawReason'] != "0") {
                    $this->addFlash(
                        'error',
                        "Cette fonctionnalité n'est pas autorisée pour cet utilisateur"
                    );
                    return $this->redirectToRoute('purchase');
                } else {
                    $this->addFlash(
                        'info',
                        $commande->getMontant() . "€ ont été débités du compte de " . $account->getFirstName()." ".$account->getLastName()."."
                    );
                }
                break;
            case 'cash':
                if ($form['withdrawReason'] == "1" && $this->security->isGranted('ROLE_INTRO')) {
                    $this->addFlash(
                        'info',
                        $commande->getMontant() . "€ ont été retirés de la caisse pour le retour de ". $commande->getMontant() ." écocup(s)."
                    );
                } elseif ($form['withdrawReason'] == "2" && $this->security->isGranted('ROLE_BUREAU')) {
                    $this->addFlash(
                        'info',
                        $commande->getMontant() . "€ ont été retirés de la caisse pour un remboursement."
                    );
                } elseif ($form['withdrawReason'] != "0") {
                    $this->addFlash(
                        'error',
                        "Cette fonctionnalité n'est pas autorisée pour cet utilisateur"
                    );
                    return $this->redirectToRoute('purchase');
                } else {
                    $this->addFlash(
                        'info',
                        $commande->getMontant() . "€ ont bien été ajoutés à la caisse."
                    );
                }
                break;
            case 'pumpkin':
                $this->addFlash(
                    'info',
                    $commande->getMontant()."€ ont été encaissés par Pumpkin."
                );
                break;
            default:
                $this->addFlash(
                    'erreur', 
                    "La méthode de paiement n'a pas été reconnue !"
                );
                return $this->redirectToRoute('purchase');
        }

        $em->flush();

        if (isset($account)) {
            $this->indexManager->index($account, $em);
        }

        return $this->redirectToRoute('purchase');
    }

    /**
     * @Route("/purchase/open", name="openCashier")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function openCashier(){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BUREAU')) {
            throw $this->createAccessDeniedException();
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
