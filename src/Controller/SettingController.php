<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends BasicController
{
    /**
     * @Route("/settings", name="settings")
     * @return Response
     */
    public function indexAction(): Response
    {
        $this->getModes();
        $this->data['settings'] = $this->getDoctrine()->getRepository(Settings::class)->findAll();
        return $this->render('settings/index.html.twig', $this->data);
    }
}
