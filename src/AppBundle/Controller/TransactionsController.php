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

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $repo_transactions = $this->getDoctrine()->getRepository('AppBundle:Transactions')->findAll();
        $data['transactions']=$repo_transactions;

        return $this->render("transactions/index.html.twig", $data);
    }
}
