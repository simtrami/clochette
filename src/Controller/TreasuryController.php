<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TreasuryController
 * @package App\Controller
 * @Route("/treasury")
 */
class TreasuryController extends BasicController
{
    /**
     * @Route("")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        return $this->render('treasury/index.html.twig', $this->data);
    }

}
