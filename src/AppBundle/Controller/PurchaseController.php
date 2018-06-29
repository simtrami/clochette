<?php
// src/AppBundle/Controller/PurchaseController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PurchaseController extends Controller
{
    
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
}