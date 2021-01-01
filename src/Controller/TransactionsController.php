<?php
// src/AppBundle/Controller/TransactionsController.php
namespace App\Controller;

use App\Entity\Transactions;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TransactionsController
 * @package App\Controller
 * @Route("/transactions")
 */
class TransactionsController extends BasicController
{
    /**
     * @Route("", name="transactions")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $repo_transactions = $this->getDoctrine()->getRepository(Transactions::class)->notRegistered();
        $this->data['transactions'] = $repo_transactions;

        return $this->render("transactions/index.html.twig", $this->data);
    }

    /**
     * @Route("/all", name="all_transactions")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function indexAll(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $allTransactions = $this->getDoctrine()->getRepository(Transactions::class)->findAllPaginated($page, $limit);
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
