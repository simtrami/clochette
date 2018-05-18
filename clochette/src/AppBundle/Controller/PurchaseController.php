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


        $bottles_notnull = $repo_stocks->findBy(
            array('type' => 'bottle', 'quantite' => 15)
        );

        $article_notnull = $repo_stocks->findBy(
            array('type' => 'article', 'quantite' => 25)
        );

        /* futs */ $sql = ' SELECT * FROM stocks S WHERE S.type="draft" AND S.quantite > :quantite ';

        $drafts_notnull = $conn->prepare($sql);
        $drafts_notnull->execute(['quantite' => 0]);

        /* bouteilles */ $sql = ' SELECT * FROM stocks S WHERE S.type="bottle" AND S.quantite > :quantite ';

        $bottles_notnull = $conn->prepare($sql);
        $bottles_notnull->execute(['quantite' => 0]);

        /* articles */ $sql = ' SELECT * FROM stocks S WHERE S.type="article" AND S.quantite > :quantite ';

        $article_notnull = $conn->prepare($sql);
        $article_notnull->execute(['quantite' => 0]);

        $data=[];
        $data['drafts_notnull'] = $drafts_notnull;
        $data['bottles_notnull'] = $bottles_notnull;
        $data['article_notnull'] = $article_notnull;

        return $this->render("purchase/index.html.twig", $data);
                                
    }
}