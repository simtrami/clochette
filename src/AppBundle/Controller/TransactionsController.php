<?php
// src/AppBundle/Controller/TransactionsController.php
namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class TransactionsController extends BasicController
{
    /**
     * @Route("/transactions", name="transactions")
     */
    public function showNotRegistered()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $repo_transactions = $this->getDoctrine()->getRepository('AppBundle:Transactions')->returnNotRegisteredTransactions();
        $this->data['transactions'] = $repo_transactions;

        return $this->render("transactions/index.html.twig", $this->data);
    }

    /**
     * @Route("/transactions/all", name="all_transactions")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAll()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $repo_transactions = $this->getDoctrine()->getRepository('AppBundle:Transactions')->findAll();
        $this->data['transactions'] = $repo_transactions;

        return $this->render("transactions/index.html.twig", $this->data);
    }
}
