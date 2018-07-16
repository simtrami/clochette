<?php
// src/AppBundle/Controller/CommandesController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CommandesController extends Controller {

    /**
     * @Route("/commandes", name="commandes")
     */
    public function showIndex(){
        /*
         * TODO:
         *  - Mettre les modes de paiement dans des Types avec Doctrine
         */

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $repo_commandes = $this->getDoctrine()->getRepository('AppBundle:Commandes')->findAll();
        $data['commandes']=$repo_commandes;

        return $this->render("commandes/index.html.twig", $data);
    }
}