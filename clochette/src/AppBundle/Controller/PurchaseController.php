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
        
        return $this->render('purchase/index.html.twig');
                                
    }
}