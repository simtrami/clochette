<?php

namespace App\Controller;

use Algolia\SearchBundle\SearchService;
use App\DataTableType\AccountTableType;
use App\DataTableType\TransactionTableType;
use App\Entity\Account;
use App\Entity\Transactions;
use App\Entity\Users;
use App\Form\AccountType;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\GreaterThan;

/**
 * Class AccountController
 * @package App\Controller
 * @Route("/accounts")
 */
class AccountController extends BasicController
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * @Route("", name="accounts_index", methods={"GET", "POST"})
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @return Response
     */
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        // TODO: add search
        $table = $dataTableFactory->createFromType(AccountTableType::class, [],
            ['order' => [[0, 'desc']],]
        )->setName('accounts-table')->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        $this->data['datatable'] = $table;

        return $this->render("accounts/index.html.twig", $this->data);
    }

    /**
     * @Route("/new", name="accounts_new", methods={"GET","POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function new(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $this->getModes();

        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);

            $em->flush();

            $this->addFlash('info', 'Un nouveau compte a été créé.');

            return $this->redirectToRoute('accounts_new');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'new';
        return $this->render(
            'accounts/edit.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/{id}", name="accounts_show", requirements={"id"="\d+"}, methods={"GET","POST"})
     * @param Account $account
     * @param Request $request
     * @param DataTableFactory $dataTableFactory
     * @return Response
     */
    public function show(Account $account, Request $request, DataTableFactory $dataTableFactory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $this->data['account'] = $account;

        $table = $dataTableFactory->createFromType(TransactionTableType::class, [
            'account' => $account->getPseudo()
        ], ['order' => [[0, 'desc']]]
        )->setName('transactions-table')->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        $this->data['datatable'] = $table;

        return $this->render("accounts/show.html.twig", $this->data);
    }

    /**
     * @Route("/{id}/edit", name="accounts_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     * @param Request $request
     * @param $account
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function edit(Request $request, Account $account)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();
            $this->addFlash('info', "Le compte de {$account->getFirstName()} {$account->getLastName()} a bien été modifié.");
            return $this->redirectToRoute('accounts_index');
        }
        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'edit';
        $this->data['firstName'] = $account->getFirstName();
        $this->data['lastName'] = $account->getLastName();
        return $this->render(
            'accounts/edit.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/{id}/refill", name="accounts_refill", requirements={"id"="\d+"}, methods={"GET","POST"})
     * @param Request $request
     * @param $account
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function refill(Request $request, Account $account)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $amount = array('message' => 'Montant du rechargement');
        $form = $this->createFormBuilder($amount)
            ->add('amount', MoneyType::class, [
                'constraints' => new GreaterThan(['value' => 0])
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $methode = $request->request->get('methode');

            if (!in_array($methode, ['cash', 'pumpkin', 'card'])) {
                $this->addFlash(
                    'error',
                    "La méthode de paiement est inconnue !"
                );
                return $this->redirectToRoute('accounts_show', ['id' => $account]);
            }

            $transaction = new Transactions();

            // Insertion du timestamp dans l'entité Transactions
            $timestamp = date_create(date("Y-m-d H:i:s"));
            $transaction->setTimestamp($timestamp);

            $transaction->setType(3);

            $em = $this->getDoctrine()->getManager();
            $repo_users = $em->getRepository(Users::class);

            $user = $repo_users->find($this->getUser());

            $amount = $form->getData()['amount'];

            $transaction->setAccount($account);
            $transaction->setStaff($user);
            $transaction->setMethod($methode);
            $transaction->setAmount($amount);

            $balance = $account->getBalance();
            $newBalance = $balance + $amount;

            $account->setBalance($newBalance);

            $em->persist($account);
            $em->persist($transaction);

            $em->flush();

            $this->addFlash('info',
                "{$form['amount']->getData()}€ ont été ajoutés au compte de {$account->getFirstName()} {$account->getLastName()}. Son solde est désormais de {$newBalance}€."
            );

            if ($methode === 'cash') {
                if ($this->getParameter('app.printer.disable')) {
                    $this->addFlash('info', 'The printer is disabled.');
                } else {
                    $printer = new Printer(new NetworkPrintConnector($this->getParameter('app.printer.ip'), $this->getParameter('app.printer.port')));
                    try {
                        $printer->pulse();
                        $this->addFlash(
                            'info',
                            "L'ouverture de la caisse a été effectuée."
                        );
                    } finally {
                        $printer->close();
                    }
                }
            }

            return $this->redirectToRoute('accounts_show', ['id' => $account->getId()]);
        }

        $this->data['form'] = $form->createView();
        $this->data['pseudo'] = $account->getPseudo();

        return $this->render('accounts/refill.html.twig', $this->data);
    }
}
