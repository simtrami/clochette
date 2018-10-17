<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TreasuryController extends Controller
{
    /**
     * @Route("/treasury")
     */
    public function showIndexAction()
    {
        return $this->render('treasury/index.html.twig', array(
            // ...
        ));
    }

}
