<?php

namespace App\Controller;

use Algolia\SearchBundle\SearchService;
use App\Entity\Account;
use App\Entity\Transactions;
use App\Entity\Users;
use App\Form\AccountType;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\GreaterThan;

class AccountController extends BasicController
{

    protected $escposPrinterPort, $escposPrinterIP;
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
        $this->escposPrinterIP = getenv('ESCPOS_PRINTER_IP');
        $this->escposPrinterPort = getenv('ESCPOS_PRINTER_PORT');
    }

    /**
     * @Route("/accounts", name="accounts")
     * @return Response
     */
    public function showIndex(): Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $accounts = $this->getDoctrine()->getRepository(Account::class)->findAll();
        $this->data['accounts'] = $accounts;

        return $this->render("accounts/index.html.twig", $this->data);
    }

    /**
     * @Route("/accounts/create", name="create_account")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function createAccount(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);

            $em->flush();

            $this->addFlash('info', 'Un nouveau compte a été créé.');

            return $this->redirectToRoute('create_account');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'new_account';
        return $this->render(
            'accounts/account.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/accounts/{id}", name="show_account", requirements={"id"="\d+"})
     * @param Account $account
     * @return Response
     */
    public function showAccount(Account $account): Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();
        $this->data['account'] = $account;
        return $this->render("accounts/show.html.twig", $this->data);
    }

    /**
     * @Route("/accounts/{id}/modify", name="modify_account", requirements={"id"="\d+"})
     * @param Request $request
     * @param $account
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function modifyAccount(Request $request, Account $account)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();

//        $repo_account = $this->getDoctrine()->getRepository(Account::class);
//        $account = $repo_account->find($id);

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();
            $this->addFlash('info', "Le compte de {$account->getFirstName()} {$account->getLastName()} a bien été modifié.");
            return $this->redirectToRoute('accounts');
        }
        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'modify_account';
        $this->data['firstName'] = $account->getFirstName();
        $this->data['lastName'] = $account->getLastName();
        return $this->render(
            'accounts/account.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/accounts/{id}/refill", name="refill_account", requirements={"id"="\d+"})
     * @param Request $request
     * @param $account
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function refillAccount(Request $request, Account $account)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();

//        $account = $this->getDoctrine()->getManager()->getRepository('AppBundle:Account')->find($id);

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
                return $this->redirectToRoute('show_account', ['id' => $account]);
            }

            $transaction = new Transactions();

            // Insertion du timestamp dans l'entité Transactions
            $timestamp = date_create(date("Y-m-d H:i:s"));
            $transaction->setTimestamp($timestamp);

            $transaction->setType(3);

            $em = $this->getDoctrine()->getManager();
//            $repo_account = $em->getRepository(Account::class);
            $repo_users = $em->getRepository(Users::class);

//            $account = $repo_account->find($account);
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
                if (getenv('NO_PRINTER')) {
                    $this->addFlash('info', 'The printer is disabled.');
                } else {
                    $connector = new NetworkPrintConnector($this->escposPrinterIP, $this->escposPrinterPort);
                    $printer = new Printer($connector);
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

            return $this->redirectToRoute('show_account', ['id' => $account->getId()]);
        }

        $this->data['form'] = $form->createView();
        $this->data['pseudo'] = $account->getPseudo();

        return $this->render('accounts/refill.html.twig', $this->data);
    }
}