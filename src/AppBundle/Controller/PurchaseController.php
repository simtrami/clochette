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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_users = $this->getDoctrine()->getRepository('AppBundle:Users');
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        
        $commande = new Commandes();
        $detailsCommande = new DetailsCommandes();

        $form = [];
        $form['userId'] = $request->request->get('userId');
        $form['methode'] = $request->request->get('methode');
        $form['drafts'] = $request->request->get('drafts');
        $form['bottles'] = $request->request->get('bottles');
        $form['articles'] = $request->request->get('articles');
        $form['total'] = $request->request->get('total');
        $form['compte'] = $request->request->get('search');
        
        $user = $repo_users->findById($form['userId']);

        if ($form['methode'] == "compte") {
            $compte = $repo_comptes->findByPseudo($form['compte']);
            $solde = $compte->getSolde();

            if ($user->getRoles() == "ROLE_INTRO" && $solde-$form['total'] < 0) {
                // Refuse
            } else {
                $newSolde = $solde - $form['total'];
            }
        }

        foreach ($form['drafts'] as $beer) {

        }

        $commande->setMontant($form['total']);
        $commande->setTimestamp(date("Y-m-d H:i:s"));
        

        return $this->render(
            'purchase/index.html.twig'
        );
    }
    
}