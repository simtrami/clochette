<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class TreasuryController extends BasicController
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
