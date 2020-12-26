<?php
// src/AppBundle/Controller/TransactionsController.php
namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function showAll(Request $request): Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $allTransactions = $this->getDoctrine()->getRepository('AppBundle:Transactions')
            ->findAllPaginated($page, $limit);
        $transactions = $allTransactions->getIterator();
        $this->data['page'] = $page;
        $this->data['limit'] = $limit;
        $this->data['transactions'] = $transactions;
        $this->data['count'] = $transactions->count();
        $this->data['total'] = $allTransactions->count();
        $this->data['maxPage'] = ceil($this->data['total'] / $limit);

        return $this->render("transactions/index.html.twig", $this->data);
    }
}
