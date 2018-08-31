<?php
// src/AppBundle/Controller/TransactionsController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class TransactionsController extends Controller {

    /**
     * @Route("/transactions", name="transactions")
     */
    public function showIndex(){
        /*
         * TODO:
         *  - Mettre les modes de paiement dans des Types avec Doctrine
         */

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $repo_commandes = $this->getDoctrine()->getRepository('AppBundle:Transactions')->findAll();
        $data['transactions']=$repo_commandes;

        return $this->render("transactions/index.html.twig", $data);
    }
}
