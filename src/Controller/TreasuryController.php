<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TreasuryController extends BasicController
{
    /**
     * @Route("/treasury")
     */
    public function showIndexAction(): Response
    {
        return $this->render('treasury/index.html.twig');
    }

}
