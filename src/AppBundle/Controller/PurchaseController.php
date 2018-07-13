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
        $conn = $this->getDoctrine()->getManager()->getConnection();


        /* futs */ $sql = ' SELECT * FROM stocks S WHERE S.type="draft" AND S.isForSale = :x AND S.quantite > 0';

        $selected_drafts = $conn->prepare($sql);
        $selected_drafts -> execute(['x' => 1]);

        /* bouteilles */ $sql = ' SELECT * FROM stocks S WHERE S.type="bottle" AND S.isForSale = :x AND S.quantite > 0';

        $selected_bottles = $conn->prepare($sql);
        $selected_bottles -> execute(['x' => 1]);

        /* articles */ $sql = ' SELECT * FROM stocks S WHERE S.type="article" AND S.isForSale = :x AND S.quantite > 0';

        $selected_articles = $conn->prepare($sql);
        $selected_articles -> execute(['x' => 1]);

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
         */
      
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_users = $this->getDoctrine()->getRepository('AppBundle:Users');
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        
        $commande = new Commandes();

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
         *   (implémenter les Flagbags)
         */
      
        $user = $repo_users->find($form['userId']);
      
        // Paiement par compte : Validation de la commande en fonction de l'utilisateur et du solde du compte
        if ($form['methode'] == "account") {
            $compte = $repo_comptes->findOneBy(['pseudo' => $form['compte']]);
            $solde = $compte->getSolde();

            if ($user->getRoles() == "ROLE_INTRO" && $solde < $form['total']) {
                /**
                 * Redirige vers la page purchase avec un message signalant et sécrivant l'erreur
                 * Actuellement c'est un 403 -> implémenter les Flagbags
                 */
                throw $this->createAccessDeniedException();
            } else {
                $newSolde = $solde - $form['total'];
            }
            // Insertion de l'user ayant validé la commande dans l'entité Commandes
                // $form['IdUser'] n'est pas utilisé au cas où cette valeur est erronée et que l'user n'a pas été trouvé
                // Même si a priori cette ligne ne serait pas exécutée dans ce cas
            $commande->setUser($user);
          
            // Insertion du compte dans l'entité Commandes
            $commande->setCompte($compte);
        }
        // Tout mode de paiement
      
        // Insertion du prix dans l'entité Commandes
        $commande->setMontant($form['total']);
        
        // Insertion du timestamp dans l'entité Commandes
        $timestamp = date_create(date("Y-m-d H:i:s"));
        $commande->setTimestamp($timestamp);
        $em->persist($commande);
      
        // Insertions des articles et de leur quantité (si non nulle) dans des entités DetailsCommandes
        foreach ($form['drafts'] as $article) {
            if ($article['quantite'] != 0) {
              $detail = new DetailsCommandes();
              $detail->setArticle($repo_stocks->find($article['id']));
              $detail->setQuantite($article['quantite']);
              $detail->setCommande($commande);
              $em->persist($detail);
            }
        }
        foreach ($form['bottles'] as $article) {
            if ($article['quantite'] != 0) {
              $detail = new DetailsCommandes();
              $detail->setArticle($repo_stocks->find($article['id']));
              $detail->setQuantite($article['quantite']);
              $detail->setCommande($commande);
              $em->persist($detail);
            }
        }
        foreach ($form['articles'] as $article) {
            if ($article['quantite'] != 0) {
              $detail = new DetailsCommandes();
              $detail->setArticle($repo_stocks->find($article['id']));
              $detail->setQuantite($article['quantite']);
              $detail->setCommande($commande);
              $em->persist($detail);
            }
        }
      
        // Modification du solde du compte
            // Effectué en dernier en cas d'erreur sur l'enregistrement de la commande (et donc si on veut la refaire)
        if ($form['methode'] == "account") {
            $compte->setSolde($newSolde);
            $em->persist($compte);
        }
      
        $em->flush();

        return $this->redirectToRoute('purchase');
    }
    
}