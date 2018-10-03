<?php
// src/AppBundle/Controller/GestionTenueController.php

namespace AppBundle\Controller;

use AppBundle\Entity\GestionTenue;
use AppBundle\Entity\Treasury;
use AppBundle\Entity\Zreport;
use AppBundle\Form\GestionTenueType;
use AppBundle\Form\TreasuryType;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Swift_Image;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GestionTenueController extends Controller{

    /*
     * @var string
     */
    protected $escposPrinterIP;

    /*
     * @var int
     */
    protected $escposPrinterPort;

    protected $mailingListAddress;

    protected $sendingAddress;

    public function __construct($escposPrinterIP, $escposPrinterPort, $mailingListAddress, $sendingAddress)
    {
        $this->escposPrinterIP = $escposPrinterIP;
        $this->escposPrinterPort = $escposPrinterPort;
        $this->mailingListAddress = $mailingListAddress;
        $this->sendingAddress = $sendingAddress;
    }

    /**
     * @Route("/gestion", name="gestion-tenue")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getData(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');

        $draft = $repo_typeStocks->returnType('Fût');
        $bottle = $repo_typeStocks->returnType('Bouteille');
        $article = $repo_typeStocks->returnType('Nourriture ou autre');

        $gestion = new GestionTenue();

        $drafts = $repo_stocks->findByType($draft);
        $bottles = $repo_stocks->findByType($bottle);
        $articles = $repo_stocks->findByType($article);

        foreach ($drafts as $draft){
            $gestion->getDrafts()->add($draft);
        }
        foreach ($bottles as $bottle){
            $gestion->getBottles()->add($bottle);
        }
        foreach ($articles as $article){
            $gestion->getArticles()->add($article);
        }

        $form = $this->createForm(GestionTenueType::class, $gestion);

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

        $data['form'] = $form->createView();

        return $this->render("gestion/index.html.twig", $data);
    }

    /**
     * @Route("/gestion/z", name="print-z")
     * @return string
     * @throws \Exception
     */
    public function printZ()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BUREAU')) {
            throw $this->createAccessDeniedException();
        }

        $repo_z = $this->getDoctrine()->getRepository('AppBundle:Zreport');
        $lastZTimestamp = $repo_z->returnLastZTimestamp()['timestamp'];

        $repo_transactions = $this->getDoctrine()->getRepository('AppBundle:Transactions');

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
                        $rechargements[$transaction->getCompte()->getPrenom() . ' ' . $transaction->getCompte()->getNom()] = [
                            "methode" => "Liquide",
                            "montant" => $transaction->getMontant()
                        ];
                        $totRechCash += $rechargements[$transaction->getCompte()->getPrenom() . ' ' . $transaction->getCompte()->getNom()]["montant"];
                    } elseif ($transaction->getMethode() == "pumpkin") {
                        $rechargements[$transaction->getCompte()->getPrenom() . ' ' . $transaction->getCompte()->getNom()] = [
                            "methode" => "Pumpkin",
                            "montant" => $transaction->getMontant()
                        ];
                        $totRechPumpkin += $rechargements[$transaction->getCompte()->getPrenom() . ' ' . $transaction->getCompte()->getNom()]["montant"];
                    } elseif ($transaction->getMethode() == "card") {
                        $rechargements[$transaction->getCompte()->getPrenom() . ' ' . $transaction->getCompte()->getNom()] = [
                            "methode" => "Carte Bleue",
                            "montant" => $transaction->getMontant()
                        ];
                        $totRechCard += $rechargements[$transaction->getCompte()->getPrenom() . ' ' . $transaction->getCompte()->getNom()]["montant"];
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
        $totRemb = $totRembCash + $totRembAccount;
        $totRech = $totRechCash + $totRechPumpkin + $totRechCard;

        // Calcul du bilan
        $totEntrees = $totCom + $totRech;
        $tot = $totEntrees - $totRemb;

        // Bilan des stocks
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');
        $typeDraft = $repo_typeStocks->returnType('Fût');
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');
        $drafts = $repo_stocks->findByType($typeDraft);
        $bottles = $repo_stocks->findByType($typeBottle);
        $others = $repo_stocks->findByType($typeArticle);

        // Génération de l'entité Zreport
        $zReport->setUser($this->getUser());
        $timestamp = date_create(date("Y-m-d H:i:s"));
        $zReport->setTimestamp($timestamp);
        $zReport->setTotalCommand($totCom);
        $zReport->setTotalRefund($totRemb);
        $zReport->setTotalRefill($totRech);
        $zReport->setTotal($tot);

        // Génération du mail
        $message = (new \Swift_Message('Ticket Z du ' . $date . ' à ' . $time))
            ->setFrom($this->sendingAddress)
            ->setTo($this->mailingListAddress);
        $logo = $message->embed(Swift_Image::fromPath('images/logo.ico'));
        $message->setBody(
            $this->renderView(
                'emails/z.html.twig',
                array(
                    'user' => $username,
                    'logo' => $logo,
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
                    'totEntrees' => $totEntrees,
                    'tot' => $tot,
                    'users' => $users,
                    'nbTransactions' => $nbTransactions,
                    'drafts' => $drafts,
                    'bottles' => $bottles,
                    'others' => $others
                )
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
            $printer->text('Émis le ' . $date . ' à ' . $time . '
par ' . $username . '
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
            foreach ($commandes["cash"] as $article => $data) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $data["qty"] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($data["price"] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total liquide
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totComCash . '
');
            $printer->feed();

            // Compte
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('COMPTE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($commandes["account"] as $article => $data) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $data["qty"] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($data["price"] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total compte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totComAccount . '
');
            $printer->feed();

            // Pumpkin
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('PUMPKIN
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($commandes["pumpkin"] as $article => $data) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $data["qty"] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($data["price"] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Pumpkin
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totComPumpkin . '
');
            $printer->feed();

            // Carte
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('CARTE
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($commandes["card"] as $article => $data) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($article . ' : ' . $data["qty"] . '
');
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text($data["price"] . '
');
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total carte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totComCard . '
');
            $printer->feed();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totCom . '
');
            $printer->feed();

            // Rechargements
            $printer->initialize();
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text('RECHARGEMENTS
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            foreach ($rechargements as $compte => $rechargement) {
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($compte . ' (' . $rechargement["methode"] . ')
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
            $printer->text($totRechCash . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Pumpkin
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totRechPumpkin . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total Carte
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totRechCard . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totRech . '
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
            $printer->text('Liquide : ' . $nbRembCash . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text('Ecocups ramenées : ' . $totEcoCash . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totRembCash . '
');
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text('Compte : ' . $nbRembAccount . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->text('Ecocups ramenées : ' . $totEcoAccount . '
');
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totRembAccount . '
');
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total remboursements
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($totRemb . '
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
            $printer->text('+ ' . $totEntrees . '
');
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total sorties
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text('- ' . $totRemb . '
');
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Total
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($tot . '
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
            foreach ($users as $user => $nb) {
                $printer->text($user . ' : ' . $nb . '
');
            }
            $printer->feed();

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Nombre total de transactions
');
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text($nbTransactions . '
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

        $em = $this->getDoctrine()->getManager();
        $em->persist($zReport);
        $em->flush();

        $this->addFlash(
            'info', "Le ticket Z vient d'être imprimé et envoyé par mail à la mailing-list !"
        );

        return $this->redirectToRoute('cloture', ['id_zreport' => $zReport->getId()]);
    }

    /**
     * @Route("/gestion/cloture/{id_zreport}", name="cloture")
     * @param Request $request
     * @param int $id_zreport
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function closeBar(Request $request,int $id_zreport)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BUREAU')) {
            throw $this->createAccessDeniedException();
        }

        $treasury = new Treasury();
        $form = $this->createForm(TreasuryType::class, $treasury);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mvtCoffre = $request->request->get('mvt-coffre');

            $doctrine = $this->getDoctrine();
            $repo_treasury = $doctrine->getRepository(Treasury::class);
            $lastTreasury = $repo_treasury->returnLastTreasury()['coffre'];
            if (!empty($lastTreasury)) {
                $treasury->setCoffre($lastTreasury + $mvtCoffre);
            } else {
                $treasury->setCoffre($mvtCoffre);
            }

            $treasury->setZreport($doctrine->getRepository(Zreport::class)->find($id_zreport));
            $em = $this->getDoctrine()->getManager();
            $em->persist($treasury);
            $em->flush();

            $this->addFlash('info', 'La trésorerie a bien été mise à jour.');

            return $this->redirectToRoute('historique_tenues');
        }

        return $this->render(
            'gestion/cloture.html.twig',
            array(
                'form' => $form->createView()
            ));
    }

    /**
     * @Route("/gestion/historique", name="historique_tenues")
     */
    public function history()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BUREAU')) {
            throw $this->createAccessDeniedException();
        }

        $zreports = $this->getDoctrine()->getRepository('AppBundle:Zreport')->findAll();

        return $this->render('gestion/historique.html.twig', array("zreports" => $zreports));
    }

    /**
     * @Route("/gestion/modifier/{id_treasury}", name="modif_cloture")
     * @param Request $request
     * @param $id_treasury
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifCloture(Request $request, $id_treasury)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BUREAU')) {
            throw $this->createAccessDeniedException();
        }

        $repo_treasury = $this->getDoctrine()->getRepository(Treasury::class);

        $treasury = $repo_treasury->find($id_treasury);

        $form = $this->createForm(TreasuryType::class, $treasury);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($treasury);
            $em->flush();

            $this->addFlash('info', "La clôture de la tenue a bien été modifiée.");

            return $this->redirectToRoute("historique_tenues");
        }

        return $this->render(
            'gestion/modifier.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/gestion/details/{id_zreport}", name="details_z")
     * @param $id_zreport
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function details($id_zreport)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BUREAU')) {
            throw $this->createAccessDeniedException();
        }

        $zreport = $this->getDoctrine()->getRepository(Zreport::class)->find($id_zreport);

        return $this->render(
            'gestion/details.html.twig',
            array(
                'zreport' => $zreport
            )
        );
    }
}
