<?php
// src/AppBundle/Controller/TransactionsController.php
namespace App\Controller;

use App\DataTableType\TransactionTableType;
use Omines\DataTablesBundle\DataTableFactory;
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
     * @Route("", name="transactions_index", methods={"GET", "POST"})
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @return Response
     */
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $table = $dataTableFactory->createFromType(TransactionTableType::class, [],
            ['order' => [[0, 'desc']],]
        )->setName('transactions-table')->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        $this->data['datatable'] = $table;

        return $this->render("transactions/index.html.twig", $this->data);
    }

    /**
     * @Route("/unregistered", name="transactions_index_unregistered", methods={"GET", "POST"})
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @return Response
     */
    public function indexUnregistered(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $table = $dataTableFactory->createFromType(TransactionTableType::class,
            ['registration' => 'unregistered'],
            ['order' => [[0, 'desc']]]
        )->setName('transactions-table')->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        $this->data['datatable'] = $table;

        return $this->render("transactions/index.html.twig", $this->data);
    }
}
