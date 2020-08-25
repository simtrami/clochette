<?php
namespace AppBundle\Controller;

use Algolia\SearchBundle\IndexManagerInterface;
use AppBundle\Entity\Account;
use AppBundle\Entity\Transactions;
use AppBundle\Form\AccountType;
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
    protected $indexManager;

    public function __construct(IndexManagerInterface $indexingManager, $escposPrinterIP, $escposPrinterPort)
    {
        $this->indexManager = $indexingManager;
        $this->escposPrinterIP = $escposPrinterIP;
        $this->escposPrinterPort = $escposPrinterPort;
    }

    /**
     * @Route("/accounts", name="accounts")
     * @return Response
     */
    public function showIndex(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $repo_account = $this->getDoctrine()->getRepository('AppBundle:Account')->findAll();
        $this->data['accounts'] = $repo_account;

        return $this->render("accounts/index.html.twig", $this->data);
    }

    /**
     * @Route("/accounts/{id}", name="show_account")
     * @param Account $account
     * @return Response
     */
    public function showAccount(Account $account)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();
        $this->data['account'] = $account;
        return $this->render("accounts/show.html.twig", $this->data);
    }

    /**
     * @Route("/accounts/new", name="create_account")
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

        $account=new Account();
        $form=$this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        //$checkAccount = $this->container->get('appbundle.checkaccount');

        if($form->isSubmitted() && $form->isValid()) {
            /*if ($checkAccount->anneeNotValid($account)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }*/

            /*if (!$checkAccount->namesValid($account)){
                throw new \Exception('Les nom, prénom et pseudo ne peuvent contenir que des lettres.');
            }*/

            $em = $this->getDoctrine()->getManager();
            $em->persist($account);

            $em->flush();

            $this->indexManager->index($account, $em);

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
     * @Route("/accounts/{id}/modify", name="modify_account")
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function modifyAccount(Request $request, $id){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();

        $repo_account = $this->getDoctrine()->getRepository('AppBundle:Account');
        $account = $repo_account->find($id);
        
        $form = $this->createForm(AccountType::class, $account);
        //$checkAccount = $this->container->get('appbundle.checkaccount');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /*if ($checkAccount->anneeNotValid($account)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }*/
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();
            $this->indexManager->index($account, $em);
            $this->addFlash('info', 'Le compte de ' .$account->getFirstName(). ' ' .$account->getLastName(). ' a bien été modifié.');
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
     * @Route("/accounts/{id}/refill", name="refill_account")
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function refillAccount(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $account = $this->getDoctrine()->getManager()->getRepository('AppBundle:Account')->find($id);

        $ammount = array('message' => 'Montant du rechargement');
        $form = $this->createFormBuilder($ammount)
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
                return $this->redirectToRoute('show_account', ['id' => $id]);
            }

            $transaction = new Transactions();

            // Insertion du timestamp dans l'entité Transactions
            $timestamp = date_create(date("Y-m-d H:i:s"));
            $transaction->setTimestamp($timestamp);

            $transaction->setType(3);

            $em = $this->getDoctrine()->getManager();
            $repo_account = $em->getRepository('AppBundle:Account');
            $repo_users = $em->getRepository('AppBundle:Users');

            $account = $repo_account->find($id);
            $user = $repo_users->find($this->getUser()->getId());

            $ammount = $form->getData()['amount'];

            $transaction->setAccount($account);
            $transaction->setUser($user);
            $transaction->setMethode($methode);
            $transaction->setMontant($ammount);

            $balance = $account->getBalance();
            $newBalance = $balance + $ammount;

            $account->setBalance($newBalance);

            $em->persist($account);
            $em->persist($transaction);

            $em->flush();

            $this->indexManager->index($account, $em);

            $this->addFlash('info',
                $form['amount']->getData() .
                '€ ont été ajoutés au compte de '.$account->getFirstName().' '.$account->getLastName().'. Son solde est désormais de '.$newBalance.'€.'
            );

            if ($methode=='cash') {
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

            return $this->redirectToRoute('show_account', ['id' => $id]);
        }

        $this->data['form'] = $form->createView();
        $this->data['pseudo'] = $account->getPseudo();

        return $this->render('accounts/refill.html.twig', $this->data);
    }
}
