<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SettingController
 * @package App\Controller
 * @Route("/settings")
 */
class SettingController extends BasicController
{
    /**
     * @Route("", name="settings_index", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();
        $this->data['settings'] = $this->getDoctrine()->getRepository(Settings::class)->findAll();
        return $this->render('settings/index.html.twig', $this->data);
    }
}
