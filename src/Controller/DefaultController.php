<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends BasicController
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        $this->getModes();
        return $this->render('home/index.html.twig', $this->data);
    }
}
