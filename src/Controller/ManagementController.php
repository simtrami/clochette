<?php

namespace App\Controller;

use App\Entity\SellsManagement;
use App\Entity\Stocks;
use App\Entity\Transactions;
use App\Entity\Treasury;
use App\Entity\TypeStocks;
use App\Entity\Zreport;
use App\Form\SellsManagementType;
use App\Form\TreasuryType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ManagementController
 * @package App\Controller
 * @Route("/management")
 */
class ManagementController extends BasicController
{
    protected $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("", name="management_index", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository(Stocks::class);
        $repo_typeStocks = $this->getDoctrine()->getRepository(TypeStocks::class);

        $typeDraft = $repo_typeStocks->returnType('Fût');
        $drafts = $repo_stocks->findBy(['type' => $typeDraft]);
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $bottles = $repo_stocks->findBy(['type' => $typeBottle]);
        $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');
        $articles = $repo_stocks->findBy(['type' => $typeArticle]);

        $management = new SellsManagement();
        foreach ($drafts as $draft){
            $management->getDrafts()->add($draft);
        }
        foreach ($bottles as $bottle){
            $management->getBottles()->add($bottle);
        }
        foreach ($articles as $article){
            $management->getArticles()->add($article);
        }

        $form = $this->createForm(SellsManagementType::class, $management);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            foreach ($drafts as $draft) {
                $em->persist($draft);
            }
            foreach ($bottles as $bottle) {
                $em->persist($bottle);
            }
            foreach ($articles as $article) {
                $em->persist($article);
            }
            $em->flush();

            $this->addFlash('info', 'La liste des articles en vente a bien été mise à jour.');

            return $this->redirectToRoute('purchase_index');
        }

        $this->data['form'] = $form->createView();

        return $this->render("management/index.html.twig", $this->data);
    }

    /**
     * @Route("/runs/new", name="management_runs_new", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     * @throws NonUniqueResultException|TransportExceptionInterface
     * @throws Exception
     */
    public function newRun(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // By default, the app is not offline, meaning the cash register is connected
        $offline = $request->get('offline') ?: false;

        $repo_z = $this->getDoctrine()->getRepository(Zreport::class);
        $lastZ = $repo_z->returnLastZTimestamp();
        if ($lastZ) {
            $lastZTimestamp = $repo_z->returnLastZTimestamp()['timestamp'];
        }

        $repo_transactions = $this->getDoctrine()->getRepository(Transactions::class);

        if (!$offline && !$this->getParameter('app.printer.disable')) {
            // Trying to open the cash-drawer before doing anything
            try {
                $printer = new Printer(new NetworkPrintConnector($this->getParameter('app.printer.ip'), $this->getParameter('app.printer.port')));
                try {
                    $printer->pulse();
                    $this->addFlash(
                        'info',
                        "L'ouverture de la caisse a été effectuée."
                    );
                } finally {
                    $printer->close();
                }
            } catch (Exception $e) {
                $this->addFlash(
                    'error',
                    "Impossible de se connecter à la caisse : veuillez vérifier les branchements"
                );
                return $this->redirectToRoute('management_index');
            }
        } else {
            $this->addFlash('info', 'The printer is disabled.');
        }

        $date = date("d/m/Y");
        $time = date("H:i:s");
        $user = $this->getUser();
        $username = $user->getUsername();

        if (!empty($lastZTimestamp)) {
            $transactions = $repo_transactions->returnTransactionsSince($lastZTimestamp);
        } else {
            $transactions = $repo_transactions->findAll();
        }

        $zReport = new Zreport();

        // Total transactions
        $nbTransactions = count($transactions);

        // Types de transactions
        $commands = [
            "cash" => [],
            "account" => [],
            "pumpkin" => [],
            "card" => [],
        ];
        $refills = [];

        // Totaux des commandes par méthode de paiement
        $totCommandsCash = 0;
        $totCommandsAccount = 0;
        $totCommandsPumpkin = 0;
        $totCommandsCard = 0;

        // Totaux des remboursements par méthode de paiement
        $totReimbursementsCash = 0;
        $totReimbursementsAccount = 0;
        // Nb écocups rendues par méthode de paiement
        $totEcocupsCash = 0;
        $totEcocupsAccount = 0;

        // Totaux des rechargements par méthode de paiement
        $totRefillsCash = 0;
        $totRefillsPumpkin = 0;
        $totRefillsCard = 0;

        // Nombre de remboursement par méthode de paiement
        $nbReimbursementsCash = 0;
        $nbReimbursementsAccount = 0;

        // Utilisateurs (staff) ayant effectués des transactions
        $users = [];

        // Transactions présentant une exception (peut apparaître plusieurs fois)
        $errors = [];

        foreach ($transactions as $transaction) {
            // Tri par type
            switch ($transaction->getType()) {
                case 1:
                    switch ($transaction->getMethod()) {
                        case "cash":
                            $totCommandsCash += $transaction->getAmount();
                            foreach ($transaction->getDetails() as $detail) {
                                if (!isset($commands["cash"][$detail->getArticle()->getName()])) {
                                    $commands["cash"][$detail->getArticle()->getName()] = array();
                                    $commands["cash"][$detail->getArticle()->getName()]["qty"] = $detail->getQuantity();
                                    $commands["cash"][$detail->getArticle()->getName()]["price"] = $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                } else {
                                    $commands["cash"][$detail->getArticle()->getName()]["qty"] += $detail->getQuantity();
                                    $commands["cash"][$detail->getArticle()->getName()]["price"] += $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                }
                            }
                            break;
                        case "account":
                            $totCommandsAccount += $transaction->getAmount();
                            foreach ($transaction->getDetails() as $detail) {
                                if (!isset($commands["account"][$detail->getArticle()->getName()])) {
                                    $commands["account"][$detail->getArticle()->getName()] = array();
                                    $commands["account"][$detail->getArticle()->getName()]["qty"] = $detail->getQuantity();
                                    $commands["account"][$detail->getArticle()->getName()]["price"] = $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                } else {
                                    $commands["account"][$detail->getArticle()->getName()]["qty"] += $detail->getQuantity();
                                    $commands["account"][$detail->getArticle()->getName()]["price"] += $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                }
                            }
                            break;
                        case "pumpkin":
                            $totCommandsPumpkin += $transaction->getAmount();
                            foreach ($transaction->getDetails() as $detail) {
                                if (!isset($commands["pumpkin"][$detail->getArticle()->getName()])) {
                                    $commands["pumpkin"][$detail->getArticle()->getName()] = array();
                                    $commands["pumpkin"][$detail->getArticle()->getName()]["qty"] = $detail->getQuantity();
                                    $commands["pumpkin"][$detail->getArticle()->getName()]["price"] = $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                } else {
                                    $commands["pumpkin"][$detail->getArticle()->getName()]["qty"] += $detail->getQuantity();
                                    $commands["pumpkin"][$detail->getArticle()->getName()]["price"] += $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                }
                            }
                            break;
                        case "card":
                            $totCommandsCard += $transaction->getAmount();
                            foreach ($transaction->getDetails() as $detail) {
                                if (!isset($commands["card"][$detail->getArticle()->getName()])) {
                                    $commands["card"][$detail->getArticle()->getName()] = array();
                                    $commands["card"][$detail->getArticle()->getName()]["qty"] = $detail->getQuantity();
                                    $commands["card"][$detail->getArticle()->getName()]["price"] = $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                } else {
                                    $commands["card"][$detail->getArticle()->getName()]["qty"] += $detail->getQuantity();
                                    $commands["card"][$detail->getArticle()->getName()]["price"] += $detail->getQuantity() * $detail->getArticle()->getSellingPrice();
                                }
                            }
                            break;
                        default:
                            $errors[] = $transaction;
                            break;
                    }
                    break;
                case 2:
                    //array_push($remboursements, $transaction);
                    if ($transaction->getMethod() === "cash") {
                        $nbReimbursementsCash++;
                        $totReimbursementsCash += $transaction->getAmount();
                        // Récupération du nb d'écocup
                        if (!$transaction->getDetails()->isEmpty()) {
                            foreach ($transaction->getDetails() as $detail) {
                                $totEcocupsCash += $detail->getQuantity();
                            }
                        }
                    } elseif ($transaction->getMethod() === "account") {
                        $nbReimbursementsAccount++;
                        $totReimbursementsAccount += $transaction->getAmount();
                        // Récupération du nb d'écocup
                        if (!$transaction->getDetails()->isEmpty()) {
                            foreach ($transaction->getDetails() as $detail) {
                                $totEcocupsAccount += $detail->getQuantity();
                            }
                        }
                    } else {
                        $errors[] = $transaction;
                    }
                    break;
                case 3:
                    if ($transaction->getMethod() === "cash") {
                        $refills[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()] = [
                            "method" => "Liquide",
                            "amount" => $transaction->getAmount()
                        ];
                        $totRefillsCash += $refills[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()]["amount"];
                    } elseif ($transaction->getMethod() === "pumpkin") {
                        $refills[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()] = [
                            "method" => "Pumpkin",
                            "amount" => $transaction->getAmount()
                        ];
                        $totRefillsPumpkin += $refills[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()]["amount"];
                    } elseif ($transaction->getMethod() === "card") {
                        $refills[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()] = [
                            "method" => "Carte Bleue",
                            "amount" => $transaction->getAmount()
                        ];
                        $totRefillsCard += $refills[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()]["amount"];
                    } else {
                        $errors[] = $transaction;
                    }
                    break;
                default:
                    $errors[] = $transaction;
                    break;
            }


            // Récupération des transactions par utilisateur (staff)
            if (is_null($transaction->getStaff())) {
                $errors[] = $transaction;
            } elseif (!isset($users[$transaction->getStaff()->getUsername()])) {
                $users[$transaction->getStaff()->getUsername()] = 1;
            } else {
                $users[$transaction->getStaff()->getUsername()]++;
            }

            $transaction->setZreport($zReport);
        }

        // Calcul du montant total des transactions par type
        $totCommands = $totCommandsCash + $totCommandsAccount + $totCommandsPumpkin + $totCommandsCard;
        $totReimbursements = $totReimbursementsCash + $totReimbursementsAccount; // < 0
        $totRefills = $totRefillsCash + $totRefillsPumpkin + $totRefillsCard;

        // Calcul du bilan
        $tot = $totCommands + $totReimbursements;

        // Bilan des stocks
        $repo_stocks = $this->getDoctrine()->getRepository(Stocks::class);
        $repo_typeStocks = $this->getDoctrine()->getRepository(TypeStocks::class);
        $typeDraft = $repo_typeStocks->returnType('Fût');
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');
        $drafts = $repo_stocks->findBy(['type' => $typeDraft]);
        $bottles = $repo_stocks->findBy(['type' => $typeBottle]);
        $others = $repo_stocks->findBy(['type' => $typeArticle]);

        // Génération de l'entité Zreport
        $zReport->setStaff($this->getUser());
        $timestamp = date_create(date("Y-m-d H:i:s"));
        $zReport->setTimestamp($timestamp);
        $zReport->setTotalCommand($totCommands);
        $zReport->setTotalRefund($totReimbursements);
        $zReport->setTotalRefill($totRefills);
        $zReport->setTotal($tot);

        $this->data = array(
            'user' => $username,
            'date' => $date,
            'time' => $time,
            'commands' => $commands,
            'totCommandsCash' => $totCommandsCash,
            'totCommandsAccount' => $totCommandsAccount,
            'totCommandsPumpkin' => $totCommandsPumpkin,
            'totCommandsCard' => $totCommandsCard,
            'totCommands' => $totCommands,
            'refills' => $refills,
            'totRefillsCash' => $totRefillsCash,
            'totRefillsPumpkin' => $totRefillsPumpkin,
            'totRefillsCard' => $totRefillsCard,
            'totRefills' => $totRefills,
            'nbReimbursementsCash' => $nbReimbursementsCash,
            'totEcocupsCash' => $totEcocupsCash,
            'totReimbursementsCash' => $totReimbursementsCash,
            'nbReimbursementsAccount' => $nbReimbursementsAccount,
            'totEcocupsAccount' => $totEcocupsAccount,
            'totReimbursementsAccount' => $totReimbursementsAccount,
            'totReimbursements' => $totReimbursements,
            'tot' => $tot,
            'users' => $users,
            'nbTransactions' => $nbTransactions,
        );
        // Print Z report
        $offline ?: $this->printZ($this->data);
        // Add stock balance sheet and send e-mail report
        $this->data['drafts'] = $drafts;
        $this->data['bottles'] = $bottles;
        $this->data['others'] = $others;
        $this->sendZ($this->data);

        $em = $this->getDoctrine()->getManager();
        $em->persist($zReport);
        // Prevent redirection fail because of a timeout
        $lastTreasury = $this->getDoctrine()->getRepository(Treasury::class)->latest();
        $treasury = new Treasury();
        $treasury->setZreport($zReport);
        if ($lastTreasury !== null) {
            $treasury->setSafe($lastTreasury->getSafe());
            $treasury->setCashRegister($lastTreasury->getCashRegister());
        } else {
            $treasury->setSafe(0);
            $treasury->setCashRegister(0);
        }
        $em->persist($treasury);
        $em->flush();

        if ($offline) {
            $this->addFlash(
                'info', "Le ticket Z vient d'être envoyé par mail à la mailing-list !"
            );
        } else {
            $this->addFlash(
                'info', "Le ticket Z vient d'être imprimé et envoyé par mail à la mailing-list !"
            );
        }

        return $this->redirectToRoute('management_runs_treasury_new', ['id' => $treasury->getId()]);
    }

    /**
     * @Route("/runs/{id}/treasury/new", name="management_runs_treasury_new", methods={"GET","POST"})
     * @param Request $request
     * @param $treasury
     * @return RedirectResponse|Response
     */
    public function newRunTreasury(Request $request, Treasury $treasury)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $doctrine = $this->getDoctrine();
        $form = $this->createForm(TreasuryType::class, $treasury);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mvtSafe = $request->request->get('mvt-coffre');

            $treasury->setSafe($treasury->getSafe() + $mvtSafe);

            $em = $doctrine->getManager();
            $em->persist($treasury);
            $em->flush();

            $this->addFlash('info', 'La trésorerie a bien été mise à jour.');

            return $this->redirectToRoute('management_runs_index');
        }

        $this->data['form'] = $form->createView();

        return $this->render(
            'management/treasury.html.twig',
            $this->data);
    }

    /**
     * @Route("/runs", name="management_runs_index", methods={"GET"})
     */
    public function runsIndex(): Response
    {
        // TODO: rewrite with pagination
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $zreports = $this->getDoctrine()->getRepository(Zreport::class)->findAll();

        $this->data['zreports'] = $zreports;

        return $this->render('management/history.html.twig', $this->data);
    }

    /**
     * @Route("/runs/{id}/treasury/edit", name="management_runs_treasury_edit", methods={"GET","POST"})
     * @param Request $request
     * @param $treasury
     * @return RedirectResponse|Response
     */
    public function editRunTreasury(Request $request, Treasury $treasury)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $form = $this->createForm(TreasuryType::class, $treasury);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($treasury);
            $em->flush();

            $this->addFlash('info', "La clôture de la tenue a bien été modifiée.");

            return $this->redirectToRoute("management_runs_index");
        }

        $this->data['form'] = $form->createView();

        return $this->render('management/edit.html.twig', $this->data);
    }

    /**
     * @Route("/runs/{id}", name="management_runs_show", methods={"GET"})
     * @param $zreport
     * @return Response
     */
    public function showRun(Zreport $zreport): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $this->data['zreport'] = $zreport;

        return $this->render(
            'management/details.html.twig',
            $this->data
        );
    }

    /**
     * @param array $data
     * @throws TransportExceptionInterface
     */
    protected function sendZ(array $data): void
    {
        // Génération du mail
        // TODO: Write it in Markdown - https://symfony.com/doc/4.4/mailer.html#rendering-markdown-content
        $email = (new TemplatedEmail())
            ->from($this->getParameter('app.mail.sender_address'))
            ->to($this->getParameter('app.mail.list_address'))
            ->subject("Ticket Z du {$data['date']} à {$data['time']}")
            ->htmlTemplate('emails/z.html.twig')
            ->context($data);
        $this->mailer->send($email);
        /*$message = (new Swift_Message("Ticket Z du {$data['date']} à {$data['time']}"))
            ->setFrom($this->sendingAddress)
            ->setTo($this->mailingListAddress);
        $data['logo'] = $message->embed(Swift_Image::fromPath('images/logo.ico'));
        $message->setBody(
            $this->renderView(
                'emails/z.html.twig',
                $data
            ),
            'text/html'
        );

        // or, you can also fetch the mailer service this way
        $this->get('mailer')->send($message);
        */
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function printZ(array $data): void
    {
        if ($this->getParameter('app.printer.disable')) {
            $this->addFlash('info', 'The printer is disabled.');
            return;
        }

        $printer = new Printer(new NetworkPrintConnector($this->getParameter('app.printer.ip'), $this->getParameter('app.printer.port')));
        try {
            // En-tete
            $printer->feed(3);
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setTextSize(3,3);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setReverseColors(true);
            $printer->text('AbsINThe
');
            $printer->setReverseColors(false);
            $printer->feed();
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text('Récapitulatif de tenue
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text("Émis le {$data['date']} à {$data['time']}
par {$data['user']}
");
            $printer->feed();

            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text('================================');
            $printer->feed(2);

            // ENTREES
            $printer->initialize();
            $printer->setTextSize(2,2);
            $printer->text('ENTRÉES
');
            $printer->feed();

            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('COMMANDES
');

            // Liquide
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('LIQUIDE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['cash'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("{$article} : {$details['qty']}
");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("{$details['price']}
");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total liquide
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totComCash']}
");
            $printer->feed();

            // Compte
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('COMPTE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['account'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("{$article} : {$details['qty']}
");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("{$details['price']}
");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total compte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totComAccount']}
");
            $printer->feed();

            // Pumpkin
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('PUMPKIN
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['pumpkin'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("{$article} : {$details['qty']}
");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("{$details['price']}
");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Pumpkin
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totComPumpkin']}
");
            $printer->feed();

            // Carte
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('CARTE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['card'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("{$article} : {$details['qty']}
");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("{$details['price']}
");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total carte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totComCard']}
");
            $printer->feed();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totCom']}
");
            $printer->feed();

            // Rechargements
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('RECHARGEMENTS
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['rechargements'] as $account => $rechargement) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("{$account} ({$rechargement["method"]})
");
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text("{$rechargement["amount"]}
");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total liquide
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRechCash']}
");
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Pumpkin
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRechPumpkin']}
");
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Carte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRechCard']}
");
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRech']}
");
            $printer->feed();

            // SORTIES
            $printer->initialize();
            $printer->setTextSize(2,2);
            $printer->text('SORTIES
');
            $printer->feed();

            // Remboursements
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('REMBOURSEMENTS
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text("Liquide : {$data['nbRembCash']}
");
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text("Ecocups ramenées : {$data['totEcoCash']}
");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRembCash']}
");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text("Compte : {$data['nbRembAccount']}
");
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text("Ecocups ramenées : {$data['totEcoAccount']}
");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRembAccount']}
");
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total remboursements
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRemb']}
");
            $printer->feed();

            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text('================================');
            $printer->feed(2);

            // Total transactions
            $printer->initialize();
            $printer->setTextSize(2,2);
            $printer->text('TOTAL
');
            $printer->feed();
            $printer->initialize();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total entrées
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totCom']}
");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total sorties
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['totRemb']}
");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['tot']}
");
            $printer->feed();

            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text('================================');
            $printer->feed(2);

            // Activité appli
            $printer->initialize();
            $printer->setTextSize(2,2);
            $printer->text('Transactions
');
            $printer->feed();

            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['users'] as $user => $nb) {
                $printer->text("{$user} : {$nb}
");
            }
            $printer->feed();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Nombre total de transactions
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("{$data['nbTransactions']}
");
            $printer->feed();

            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text('================================');
            $printer->feed(2);

            // Rappel
            $printer->feed();
            $printer->setTextSize(2,2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text('RESTE A COMPTER
LA CAISSE
');
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text('Au revoir :-)
');
            $printer->feed();
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text('Ticket magique fabriqué par Clochette');

            $printer->feed(8);
        } finally {
            $printer->close();
        }
    }
}
