<?php
// src/AppBundle/Controller/PurchaseController.php
namespace AppBundle\Controller;

use Algolia\SearchBundle\IndexManagerInterface;
use AppBundle\Entity\Transactions;
use AppBundle\Entity\DetailsTransactions;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class PurchaseController extends Controller
{
    private $indexManager;

    /*
     * @var string
     */
    private $algoliaAppId;

    /*
     * @var string
     */
    private $algoliaApiSearchKey;

    /*
     * @var string
     */
    private $algoliaIndex;

    /*
     * @var string
     */
    private $escposPrinterIP;

    /*
     * @var int
     */
    private $escposPrinterPort;


    public function __construct($algoliaAppId, $algoliaApiSearchKey, $algoliaIndex, IndexManagerInterface $indexingManager, $escposPrinterIP, $escposPrinterPort)
    {
        $this->algoliaAppId = $algoliaAppId;
        $this->algoliaApiSearchKey = $algoliaApiSearchKey;
        $this->algoliaIndex = $algoliaIndex;
        $this->indexManager = $indexingManager;
        $this->escposPrinterIP = $escposPrinterIP;
        $this->escposPrinterPort = $escposPrinterPort;
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

        $draft = $repo_typeStocks->returnType('Draft');
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
     * @throws \Exception
     */
    public function validateTransaction(Request $request){
        /**
         * TODO
         * VERIFIER QUE LE FORMULAIRE A BIEN ETE PASSE ET QU'ON N'A PAS JUSTE ECRIT L'URL
         * if $request->isMethod('POST') ?
         */
      
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_users = $this->getDoctrine()->getRepository('AppBundle:Users');
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        $session = $request->getSession();

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
        $form['compte'] = $request->request->get('accountPseudo');
        
        /**
         * TODO SUR LA CLASSE :
         * - EMPECHER DE FAIRE QUOI QUE CE SOIT SI LA COMMANDE EST VIDE (qte nulles OU total nul OU mode de paiement nul)
         * - METTRE DES TESTS UN PEU PARTOUT POUR POUVOIR RENDER LA PAGE AVEC DES STATUTS D'ERREUR
         *   (implémenter les Flashbags)
        */

        // Insertion de la méthode de paiement dans l'entité Transactions
        $commande->setMethode($form['methode']);
      
        // Insertions des articles et de leur quantité (si non nulle) dans des entités DetailsTransactions
        // et calul du montant de la commande (car possibilité d'injecter une fausse valeur en html)
        $montant = 0;
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
      
        if ($montant <= 0) {
            $session->getFlashbag()->add(
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
            $compte = $repo_comptes->findOneBy(['pseudo' => $form['compte']]);
            $solde = $compte->getSolde();
            //$diff  = $montant - $solde;

            if ( $user->getRoles() == "ROLE_INTRO" &&  $solde < $montant) {

                $session->getFlashbag()->add(
                    'erreur',
                    "Le solde du compte de ".$compte->getPrenom()." ".$compte->getNom()." est insuffisant pour valider la commande.</br>
                     Il manque ". $montant - $solde ."€."
                );
                return $this->redirectToRoute('purchase');            
            } else {
                $newSolde = $solde - $montant;
            }
            // Insertion du compte dans l'entité Transactions
            $commande->setCompte($compte);
          
            // Modification du solde du compte
            $compte->setSolde($newSolde);
            // Mise à jour du compte dans la base
            $em->persist($compte);
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
                $session->getFlashbag()->add(
                    'info',
                    $commande->getMontant()."€ ont été débités du compte de ".$compte->getPrenom()." ".$compte->getNom()."."
                );
                break;
            case 'cash':
                $session->getFlashbag()->add(
                    'info', 
                    $commande->getMontant()."€ ont été payés en liquide."
                );
                break;
            case 'pumpkin':
                $session->getFlashbag()->add(
                    'info', 
                    $commande->getMontant()."€ ont été payés par Pumpkin."
                );
                break;
            default:
                $session->getFlashbag()->add(
                    'erreur', 
                    "La méthode de paiement n'a pas été reconnue."
                );
                return $this->redirectToRoute('purchase');
        }

        $em->flush();

        if (isset($compte)) {
            $this->indexManager->index($compte, $em);
        }

        return $this->redirectToRoute('purchase');
    }
    
}
