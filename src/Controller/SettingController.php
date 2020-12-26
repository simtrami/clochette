<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class SettingController extends BasicController
{
    /**
     * @Route("/settings", name="settings")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->getModes();
        $this->data['settings'] = $this->getDoctrine()->getRepository('AppBundle:Settings')->findAll();
        return $this->render('settings/index.html.twig', $this->data);
    }
}
