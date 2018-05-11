<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StockController extends Controller {

    private $draft_beer = [
            [  'id' => 1,
                'name' => 'Pinte Kro', 
                'price' => 3,
                'nbBarrels' => 4,
                'nbSoldeCurrent' => 21
            ],
            [  'id' => 2 , 
                'name' => 'Pinte Cidre', 
                'price' => 2.5,
                'nbBarrels' => 5,
                'nbSoldeCurrent' => 12
            ],
            [  'id' => 3 , 
                'name' => 'Pinte Grim Rouge', 
                'price' => 4,
                'nbBarrels' => 7,
                'nbSoldeCurrent' => 32
            ]
        ];
    private $bottled_beer = [
            [  'id' => 17 , 
                'name' => 'Leffe Blonde (33cL)', 
                'price' => 2,
                'nbBottles' => 44,
                'nbSoldeCurrent' => 12
            ],
            [  'id' => 19 , 
                'name' => 'Skoll (33cL)', 
                'price' => 2,
                'nbBottles' => 134,
                'nbSoldeCurrent' => 22
            ]
        ];
    private $food = [
            [  'id' => 15 , 
                'name' => 'Saucisson', 
                'price' => 2,
                'nbArticles' => 4,
                'nbSoldeCurrent' => 1
            ],
            [  'id' => 25 , 
                'name' => 'Pizza', 
                'price' => 3,
                'nbArticles' => 11,
                'nbSoldeCurrent' => 2
            ]
        ];
    /**
    * @Route("/stock", name="stock")
    **/

    public function showIndex(){
        $stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks')->findAll();

        $data=[];
        $data['drafts'] = $this->draft_beer;
        $data['bottles'] = $this->bottled_beer;
        $data['food'] = $this->food;

        $data['stocks'] = $stocks;
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