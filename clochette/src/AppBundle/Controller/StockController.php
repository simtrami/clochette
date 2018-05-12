<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class StockController extends Controller {

/*
    private $draft_beer = [
            [  'id' => 1,
                'name' => 'Kronembourg (50L)',
                'volume' => 50,
                'cost' => 120,
                'price' => 3,
                'nbBarrels' => 4,
                'nbSoldeCurrent' => 21
            ],
            [  'id' => 2 , 
                'name' => 'Cidre (30L)', 
                'volume' => 30,
                'cost' => 90,
                'price' => 2.5,
                'nbBarrels' => 5,
                'nbSoldeCurrent' => 12
            ],
            [  'id' => 3 , 
                'name' => 'Grimbergen Rouge (30L)',
                'volume' => 30,
                'cost' => 150,
                'price' => 4,
                'nbBarrels' => 7,
                'nbSoldeCurrent' => 32
            ]
        ];
    private $bottled_beer = [
            [  'id' => 17 , 
                'name' => 'Leffe Blonde (33cL)',
                'cost' => 1.2,
                'price' => 2,
                'nbBottles' => 44,
                'nbSoldeCurrent' => 12
            ],
            [  'id' => 19 , 
                'name' => 'Skoll (33cL)', 
                'cost' => 1.8,
                'price' => 2,
                'nbBottles' => 134,
                'nbSoldeCurrent' => 22
            ]
        ];
    private $article = [
            [  'id' => 15 , 
                'name' => 'Saucisson', 
                'cost' => 1.1,
                'price' => 2,
                'nbArticles' => 4,
                'nbSoldeCurrent' => 1
            ],
            [  'id' => 25 , 
                'name' => 'Pizza', 
                'cost' => 1.3,
                'price' => 3,
                'nbArticles' => 11,
                'nbSoldeCurrent' => 2
            ]
        ];
        */
    
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
}