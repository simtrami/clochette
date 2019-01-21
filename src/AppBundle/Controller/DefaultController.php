<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BasicController
{
    /**
     * @Route("/", name="homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->getModes();
        return $this->render('home/index.html.twig', $this->data);
    }
}
