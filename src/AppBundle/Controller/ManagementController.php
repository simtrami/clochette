<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SellsManagement;
use AppBundle\Entity\Treasury;
use AppBundle\Entity\Zreport;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Swift_Image;
use Swift_Message;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManagementController extends BasicController
{
    protected $escposPrinterIP, $escposPrinterPort;
    protected $mailingListAddress, $sendingAddress;

    public function __construct($escposPrinterIP, $escposPrinterPort, $mailingListAddress, $sendingAddress)
    {
        $this->escposPrinterIP = $escposPrinterIP;
        $this->escposPrinterPort = $escposPrinterPort;
        $this->mailingListAddress = $mailingListAddress;
        $this->sendingAddress = $sendingAddress;
    }

    /**
     * @Route("/management", name="manage-sells")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function manageSells(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');

        $typeDraft = $repo_typeStocks->returnType('Fût');
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');

        $management = new SellsManagement();

        $drafts = $repo_stocks->findBy(['type' => $typeDraft]);
        $bottles = $repo_stocks->findBy(['type' => $typeBottle]);
        $articles = $repo_stocks->findBy(['type' => $typeArticle]);

        foreach ($drafts as $draft){
            $management->getDrafts()->add($draft);
        }
        foreach ($bottles as $bottle){
            $management->getBottles()->add($bottle);
        }
        foreach ($articles as $article){
            $management->getArticles()->add($article);
        }

        $form = $this->createForm('AppBundle\Form\SellsManagementType', $management);

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

            return $this->redirectToRoute('purchase');
        }

        $this->data['form'] = $form->createView();

        return $this->render("management/index.html.twig", $this->data);
    }

    /**
     * @Route("/management/runs/processing", name="processing-run")
     * @param Request $request
     * @return string
     */
    public function registerRun(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $offline = $request->get('offline');

        $repo_z = $this->getDoctrine()->getRepository('AppBundle:Zreport');
        $lastZTimestamp = $repo_z->returnLastZTimestamp()['timestamp'];

        $repo_transactions = $this->getDoctrine()->getRepository('AppBundle:Transactions');

        try {
            if (!$offline) {
                // Trying to open the cash-drawer before doing anything
                try {
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
                } catch (Exception $e) {
                    $this->addFlash(
                        'error',
                        "Impossible de se connecter à la caisse : veuillez vérifier les branchements"
                    );
                    return $this->redirectToRoute('manage-sells');
                }
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
            $commandes = [
                "cash" => array(),
                "account" => array(),
                "pumpkin" => array(),
                "card" => array()
            ];
            //$remboursements = array();
            $rechargements = array();

            // Totaux des commandes par méthode de paiement
            $totComCash = 0;
            $totComAccount = 0;
            $totComPumpkin = 0;
            $totComCard = 0;

            // Totaux des remboursements par méthode de paiement
            $totRembCash = 0;
            $totRembAccount = 0;
            // Nb écocups rendues par méthode de paiement
            $totEcoCash = 0;
            $totEcoAccount = 0;

            // Totaux des rechargements par méthode de paiement
            $totRechCash = 0;
            $totRechPumpkin = 0;
            $totRechCard = 0;

            // Nombre de remboursement par méthode de paiement
            $nbRembCash = 0;
            $nbRembAccount = 0;

            // Utilisateurs (staff) ayant effectués des transactions
            $users = array();

            // Transactions présentant une exception (peut apparaître plusieurs fois)
            $erreurs = array();

            foreach ($transactions as $transaction) {
                // Tri par type
                switch ($transaction->getType()) {
                    case 1:
                        switch ($transaction->getMethode()) {
                            case "cash":
                                $totComCash += $transaction->getMontant();
                                foreach ($transaction->getDetails() as $detail) {
                                    if (!isset($commandes["cash"][$detail->getArticle()->getNom()])) {
                                        $commandes["cash"][$detail->getArticle()->getNom()] = array();
                                        $commandes["cash"][$detail->getArticle()->getNom()]["qty"] = $detail->getQuantite();
                                        $commandes["cash"][$detail->getArticle()->getNom()]["price"] = $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    } else {
                                        $commandes["cash"][$detail->getArticle()->getNom()]["qty"] += $detail->getQuantite();
                                        $commandes["cash"][$detail->getArticle()->getNom()]["price"] += $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    }
                                }
                                break;
                            case "account":
                                $totComAccount += $transaction->getMontant();
                                foreach ($transaction->getDetails() as $detail) {
                                    if (!isset($commandes["account"][$detail->getArticle()->getNom()])) {
                                        $commandes["account"][$detail->getArticle()->getNom()] = array();
                                        $commandes["account"][$detail->getArticle()->getNom()]["qty"] = $detail->getQuantite();
                                        $commandes["account"][$detail->getArticle()->getNom()]["price"] = $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    } else {
                                        $commandes["account"][$detail->getArticle()->getNom()]["qty"] += $detail->getQuantite();
                                        $commandes["account"][$detail->getArticle()->getNom()]["price"] += $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    }
                                }
                                break;
                            case "pumpkin":
                                $totComPumpkin += $transaction->getMontant();
                                foreach ($transaction->getDetails() as $detail) {
                                    if (!isset($commandes["pumpkin"][$detail->getArticle()->getNom()])) {
                                        $commandes["pumpkin"][$detail->getArticle()->getNom()] = array();
                                        $commandes["pumpkin"][$detail->getArticle()->getNom()]["qty"] = $detail->getQuantite();
                                        $commandes["pumpkin"][$detail->getArticle()->getNom()]["price"] = $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    } else {
                                        $commandes["pumpkin"][$detail->getArticle()->getNom()]["qty"] += $detail->getQuantite();
                                        $commandes["pumpkin"][$detail->getArticle()->getNom()]["price"] += $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    }
                                }
                                break;
                            case "card":
                                $totComCard += $transaction->getMontant();
                                foreach ($transaction->getDetails() as $detail) {
                                    if (!isset($commandes["card"][$detail->getArticle()->getNom()])) {
                                        $commandes["card"][$detail->getArticle()->getNom()] = array();
                                        $commandes["card"][$detail->getArticle()->getNom()]["qty"] = $detail->getQuantite();
                                        $commandes["card"][$detail->getArticle()->getNom()]["price"] = $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    } else {
                                        $commandes["card"][$detail->getArticle()->getNom()]["qty"] += $detail->getQuantite();
                                        $commandes["card"][$detail->getArticle()->getNom()]["price"] += $detail->getQuantite() * $detail->getArticle()->getPrixVente();
                                    }
                                }
                                break;
                            default:
                                array_push($erreurs, $transaction);
                                break;
                        }
                        break;
                    case 2:
                        //array_push($remboursements, $transaction);
                        if ($transaction->getMethode() == "cash") {
                            $nbRembCash++;
                            $totRembCash += $transaction->getMontant();
                            // Récupération du nb d'écocup
                            if (!$transaction->getDetails()->isEmpty()) {
                                foreach ($transaction->getDetails() as $detail) {
                                    $totEcoCash += $detail->getQuantite();
                                }
                            }
                        } elseif ($transaction->getMethode() == "account") {
                            $nbRembAccount++;
                            $totRembAccount += $transaction->getMontant();
                            // Récupération du nb d'écocup
                            if (!$transaction->getDetails()->isEmpty()) {
                                foreach ($transaction->getDetails() as $detail) {
                                    $totEcoAccount += $detail->getQuantite();
                                }
                            }
                        } else {
                            array_push($erreurs, $transaction);
                        }
                        break;
                    case 3:
                        if ($transaction->getMethode() == "cash") {
                            $rechargements[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()] = [
                                "methode" => "Liquide",
                                "montant" => $transaction->getMontant()
                            ];
                            $totRechCash += $rechargements[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()]["montant"];
                        } elseif ($transaction->getMethode() == "pumpkin") {
                            $rechargements[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()] = [
                                "methode" => "Pumpkin",
                                "montant" => $transaction->getMontant()
                            ];
                            $totRechPumpkin += $rechargements[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()]["montant"];
                        } elseif ($transaction->getMethode() == "card") {
                            $rechargements[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()] = [
                                "methode" => "Carte Bleue",
                                "montant" => $transaction->getMontant()
                            ];
                            $totRechCard += $rechargements[$transaction->getAccount()->getFirstName() . ' ' . $transaction->getAccount()->getLastName()]["montant"];
                        } else {
                            array_push($erreurs, $transaction);
                        }
                        break;
                    default:
                        array_push($erreurs, $transaction);
                        break;
                }


                // Récupération des transactions par utilisateur (staff)
                if (is_null($transaction->getUser())) {
                    array_push($erreurs, $transaction);
                } elseif (!isset($users[$transaction->getUser()->getUsername()])) {
                    $users[$transaction->getUser()->getUsername()] = 1;
                } else {
                    $users[$transaction->getUser()->getUsername()]++;
                }

                $transaction->setZreport($zReport);
            }

            // Calcul du montant total des transactions par type
            $totCom = $totComCash + $totComAccount + $totComPumpkin + $totComCard;
            $totRemb = $totRembCash + $totRembAccount; // < 0
            $totRech = $totRechCash + $totRechPumpkin + $totRechCard;

            // Calcul du bilan
            $tot = $totCom + $totRemb;

            // Bilan des stocks
            $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
            $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');
            $typeDraft = $repo_typeStocks->returnType('Fût');
            $typeBottle = $repo_typeStocks->returnType('Bouteille');
            $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');
            $drafts = $repo_stocks->findBy(['type' => $typeDraft]);
            $bottles = $repo_stocks->findBy(['type' => $typeBottle]);
            $others = $repo_stocks->findBy(['type' => $typeArticle]);

            // Génération de l'entité Zreport
            $zReport->setUser($this->getUser());
            $timestamp = date_create(date("Y-m-d H:i:s"));
            $zReport->setTimestamp($timestamp);
            $zReport->setTotalCommand($totCom);
            $zReport->setTotalRefund($totRemb);
            $zReport->setTotalRefill($totRech);
            $zReport->setTotal($tot);

            $this->data = array(
                'user' => $username,
                'date' => $date,
                'time' => $time,
                'commandes' => $commandes,
                'totComCash' => $totComCash,
                'totComAccount' => $totComAccount,
                'totComPumpkin' => $totComPumpkin,
                'totComCard' => $totComCard,
                'totCom' => $totCom,
                'rechargements' => $rechargements,
                'totRechCash' => $totRechCash,
                'totRechPumpkin' => $totRechPumpkin,
                'totRechCard' => $totRechCard,
                'totRech' => $totRech,
                'nbRembCash' => $nbRembCash,
                'totEcoCash' => $totEcoCash,
                'totRembCash' => $totRembCash,
                'nbRembAccount' => $nbRembAccount,
                'totEcoAccount' => $totEcoAccount,
                'totRembAccount' => $totRembAccount,
                'totRemb' => $totRemb,
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
            $em->flush();

            // Prevent redirection fail because of a timeout
            $treasury = new Treasury();
            $treasury->setZreport($zReport);
            $doctrine = $this->getDoctrine();
            $lastTreasury = $doctrine->getRepository(Treasury::class)->returnLastTreasury();
            $treasury->setCoffre('0.00');
            $treasury->setCaisse('0.00');
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

            return $this->redirectToRoute('register-treasury', ['id_zreport' => $zReport->getId(), 'id_treasury' => $treasury->getId()]);
        } catch (Exception $e) {
            // If it fails, does nothing and goes back
            $this->addFlash(
                'error', "Une erreur est survenue dans l'impression du ticket ou l'envoi du mail !"
            );
            return $this->redirectToRoute('manage-sells');
        }
    }

    /**
     * @Route("/management/runs/register/{id_treasury}", name="register-treasury")
     * @param Request $request
     * @param $id_treasury
     * @return RedirectResponse|Response
     */
    public function registerTreasury(Request $request, $id_treasury)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $doctrine = $this->getDoctrine();
        $lastTreasury = $doctrine->getRepository(Treasury::class)->returnLastTreasury();
        $treasury = new Treasury();
        $form = $this->createForm('AppBundle\Form\TreasuryType', $treasury);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mvtCoffre = $request->request->get('mvt-coffre');

            $treasury = $doctrine->getRepository(Treasury::class)->find($id_treasury);
            $treasury->setCaisse($form->get('caisse')->getData());
            if (!empty($lastTreasury)) {
                $treasury->setCoffre($lastTreasury['coffre'] + $mvtCoffre);
            } else {
                $treasury->setCoffre($mvtCoffre);
            }

            $em = $doctrine->getManager();
            $em->persist($treasury);
            $em->flush();

            $this->addFlash('info', 'La trésorerie a bien été mise à jour.');

            return $this->redirectToRoute('runs-history');
        }

        $this->data['form'] = $form->createView();

        return $this->render(
            'management/treasury.html.twig',
            $this->data);
    }

    /**
     * @Route("/management/runs/history", name="runs-history")
     */
    public function runsHistory()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $zreports = $this->getDoctrine()->getRepository('AppBundle:Zreport')->findAll();

        $this->data['zreports'] = $zreports;

        return $this->render('management/history.html.twig', $this->data);
    }

    /**
     * @Route("/management/runs/modify/{id_treasury}", name="modify-treasury")
     * @param Request $request
     * @param $id_treasury
     * @return RedirectResponse|Response
     */
    public function modifyTreasury(Request $request, $id_treasury)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $repo_treasury = $this->getDoctrine()->getRepository(Treasury::class);

        $treasury = $repo_treasury->find($id_treasury);

        $form = $this->createForm('AppBundle\Form\TreasuryType', $treasury);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($treasury);
            $em->flush();

            $this->addFlash('info', "La clôture de la tenue a bien été modifiée.");

            return $this->redirectToRoute("runs-history");
        }

        $this->data['form'] = $form->createView();

        return $this->render(
            'management/modify.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/management/runs/details/{id_zreport}", name="run-details")
     * @param $id_zreport
     * @return Response
     */
    public function runDetails($id_zreport)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $zreport = $this->getDoctrine()->getRepository(Zreport::class)->find($id_zreport);

        $this->data['zreport'] = $zreport;

        return $this->render(
            'management/details.html.twig',
            $this->data
        );
    }

    /**
     * @param array $data
     */
    protected function sendZ(array $data) {
        // Génération du mail
        $message = (new Swift_Message('Ticket Z du ' . $data['date'] . ' à ' . $data['time']))
            ->setFrom($this->sendingAddress)
            ->setTo($this->mailingListAddress);
        $data['logo'] = $message->embed(Swift_Image::fromPath('images/logo.ico'));
        $message->setBody(
            $this->renderView(
                'emails/z.html.twig',
                $data
            ),
            'text/html'
        )/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        //$mailer->send($message);

        // or, you can also fetch the mailer service this way
        $this->get('mailer')->send($message);
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function printZ(array $data) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $connector = new NetworkPrintConnector($this->escposPrinterIP, $this->escposPrinterPort);
        $printer = new Printer($connector);
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
            $printer->text('Émis le ' . $data['date'] . ' à ' . $data['time'] . '
par ' . $data['user'] . '
');
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
                $printer->text($article . ' : ' . $details['qty'] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($details['price'] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total liquide
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totComCash'] . '
');
            $printer->feed();

            // Compte
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('COMPTE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['account'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $details['qty'] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($details['price'] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total compte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totComAccount'] . '
');
            $printer->feed();

            // Pumpkin
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('PUMPKIN
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['pumpkin'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $details['qty'] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($details['price'] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Pumpkin
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totComPumpkin'] . '
');
            $printer->feed();

            // Carte
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('CARTE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['commandes']['card'] as $article => $details) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $details['qty'] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($details['price'] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total carte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totComCard'] . '
');
            $printer->feed();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totCom'] . '
');
            $printer->feed();

            // Rechargements
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('RECHARGEMENTS
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($data['rechargements'] as $account => $rechargement) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($account . ' (' . $rechargement["methode"] . ')
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($rechargement["montant"] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total liquide
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRechCash'] . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Pumpkin
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRechPumpkin'] . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Carte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRechCard'] . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRech'] . '
');
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
            $printer->text('Liquide : ' . $data['nbRembCash'] . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text('Ecocups ramenées : ' . $data['totEcoCash'] . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRembCash'] . '
');
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text('Compte : ' . $data['nbRembAccount'] . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text('Ecocups ramenées : ' . $data['totEcoAccount'] . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRembAccount'] . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total remboursements
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRemb'] . '
');
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
            $printer->text($data['totCom'] . '
');
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total sorties
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['totRemb'] . '
');
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['tot'] . '
');
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
                $printer->text($user . ' : ' . $nb . '
');
            }
            $printer->feed();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Nombre total de transactions
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($data['nbTransactions'] . '
');
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
