<?php
// src/AppBundle/Controller/ComptesController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Comptes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CompteController extends Controller {

    /**
     * @Route("/clients/display", name="comptes")
     */
    public function showIndex(){
        $em = $this->getDoctrine()->getManager();
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes')->findAll();
        $data['comptes']=$repo_comptes;

        return $this->render("comptes/comptes.html.twig", $data);
        
    }

}