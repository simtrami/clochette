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
        // Redirect to Purchase if authenticated
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('purchase_index');
        }

        $this->getModes();
        return $this->render('home/index.html.twig', $this->data);
    }
}
