<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Stocks;

class StockController extends Controller {
    
    /**
    * @Route("/stock", name="stock")
    **/
    public function showIndex(){
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');

        $drafts = $repo_stocks->findByType('draft');
        $bottles = $repo_stocks->findByType('bottle');
        $article = $repo_stocks->findByType('article');

        $data=[];
        $data['drafts'] = $drafts;
        $data['bottles'] = $bottles;
        $data['article'] = $article;


        return $this->render("stock/index.html.twig", $data);
    }

    /**
     * @Route("/stock/{id_article}", name="details")
     */
    public function showDetails(Request $request, $id_article){

        $data['id_article'] = $id_article;
        return $this->render("stock/details.html.twig", $data);
    }
    
    /**
     * @Route("/stock/ajout", name="ajout")
     */
    public function showAjout(Request $request){

        return $this->render("stock/ajout.html.twig");
    }
}