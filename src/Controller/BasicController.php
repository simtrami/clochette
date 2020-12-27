<?php

namespace App\Controller;

use App\Entity\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BasicController extends AbstractController
{
    public $data = [];

    public function getModes(): array
    {
        $modesRepository = $this->getDoctrine()->getRepository(Settings::class)->findBy(['type' => 'mode']);
        $activeModes = [];
        foreach ($modesRepository as $mode) {
            if ($mode->getParameters()["state"] == 1) {
                $activeModes[$mode->getName()] = str_replace(' ', '', strtolower($mode->getName()));
            }
        }
        if (!empty($activeModes)) {
            $this->data = ['activeModes' => $activeModes];
        } else {
            $this->data = ['activeModes' => []];
        }
        return $this->data;
    }
}
