<?php
// src/AppBundle/Controller/PurchaseController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Comptes;
use AppBundle\Entity\Commandes;
use AppBundle\Entity\DetailsCommandes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Algolia\SearchBundle\IndexManagerInterface;

class PurchaseController extends Controller
{
    protected $indexManager;

    public function __construct(IndexManagerInterface $indexingManager)
    {
        $this->indexManager = $indexingManager;
    }

    /**
     * @Route("/purchase", name="purchase")
     **/
    public function showIndex()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
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

        return $this->render("purchase/index.html.twig", $data);
    }

    /**
     * @Route("/purchase/validation", name="purchaseValidation")
     */
    public function validateCommande(Request $request){
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

        $commande = new Commandes();

        // Insertion du timestamp dans l'entité Commandes
        $timestamp = date_create(date("Y-m-d H:i:s"));
        $commande->setTimestamp($timestamp);

        $form = [];
        $form['userId'] = $request->request->get('userId');
        $form['methode'] = $request->request->get('methode');
        $form['drafts'] = $request->request->get('drafts');
        $form['bottles'] = $request->request->get('bottles');
        $form['articles'] = $request->request->get('articles');
        $form['total'] = $request->request->get('total');
        $form['compte'] = $request->request->get('search');
        
        /**
         * TODO SUR LA CLASSE :
         * - EMPECHER DE FAIRE QUOI QUE CE SOIT SI LA COMMANDE EST VIDE (qte nulles OU total nul OU mode de paiement nul)
         * - METTRE DES TESTS UN PEU PARTOUT POUR POUVOIR RENDER LA PAGE AVEC DES STATUTS D'ERREUR
         *   (implémenter les Flashbags)
        */
      
        $user = $repo_users->find($form['userId']);
      
        // Paiement par compte : Validation de la commande en fonction de l'utilisateur et du solde du compte
        if ($form['methode'] == "account") {
            $compte = $repo_comptes->findOneBy(['pseudo' => $form['compte']]);
            $solde = $compte->getSolde();
            $diff  = $form['total']-$solde;

            if (/* $user->getRoles() == "ROLE_INTRO" &&  */$solde < $form['total']) {

                $session->getFlashbag()->add(
                    'erreur',
                    'Le solde du compte de ' .$compte->getPrenom(). ' ' .$compte->getNom(). ' est insuffisant pour valider la commande.
                     Il manque ' .$diff. '€.'
                );

                return $this->redirectToRoute('purchase');
                /*throw $this->createAccessDeniedException();*/            
            } else {
                $newSolde = $solde - $form['total'];
            }
            // Insertion du compte dans l'entité Commandes
            $commande->setCompte($compte);
        }
        // Tout mode de paiement

        // Insertion de l'user ayant validé la commande dans l'entité Commande
        $commande->setUser($user);
      
        // Insertion du prix dans l'entité Commandes
        $commande->setMontant($form['total']);

        // Insertion de la méthode de paiement dans l'entité Commandes
        $commande->setMethode($form['methode']);

        // Insertion de la commande dans la base
        $em->persist($commande);
      
        // Insertions des articles et de leur quantité (si non nulle) dans des entités DetailsCommandes
        foreach ($form['drafts'] as $item) {
            if ($item['quantite'] != 0) {
              $detail = new DetailsCommandes();
              $article = $repo_stocks->find($item['id']);
              $detail->setArticle($article);
              $detail->setQuantite($item['quantite']);
              $detail->setCommande($commande);
              $em->persist($detail);
            }
        }
        foreach ($form['bottles'] as $item) {
            if ($item['quantite'] != 0) {
              $detail = new DetailsCommandes();
              $article = $repo_stocks->find($item['id']);
              $detail->setArticle($article);
              $detail->setQuantite($item['quantite']);
              $detail->setCommande($commande);
              $article->setQuantite($article->getQuantite() - $item['quantite']);
              $em->persist($detail);
              $em->persist($article);
            }
        }
        foreach ($form['articles'] as $item) {
            if ($item['quantite'] != 0) {
              $detail = new DetailsCommandes();
              $article = $repo_stocks->find($item['id']);
              $detail->setArticle($article);
              $detail->setQuantite($item['quantite']);
              $detail->setCommande($commande);
              $article->setQuantite($article->getQuantite() - $item['quantite']);
              $em->persist($detail);
              $em->persist($article);
            }
        }
        
        // Modification du solde du compte
            // Effectué en dernier en cas d'erreur sur l'enregistrement de la commande (et donc si on veut la refaire)
        if ($form['methode']== "account") {
            $compte->setSolde($newSolde);
            $em->persist($compte);

            $session->getFlashbag()->add('info', $commande->getMontant().'€ ont été débités du compte de ' .$compte->getPrenom(). ' ' .$compte->getNom().'.');

        }

        if ($form['methode'] == "cash"){
            
            $session->getFlashbag()->add(
                'info', 
                $commande->getMontant().'€ ont été payés en cash.'
            );
        }

        if ($form['methode'] == "pumpkin"){
            
            $session->getFlashbag()->add(
                'info', 
                $commande->getMontant().'€ ont été payés par Pumpkin.'
            );
        }

      
        $em->flush();


        return $this->redirectToRoute('purchase');
    }
    
}