<?php
namespace AppBundle\Controller;

use Algolia\SearchBundle\IndexManagerInterface;
use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use AppBundle\Entity\Transactions;

class AccountController extends Controller {

    private $indexManager;

    /*
     * @var string
     */
    protected $escposPrinterIP;

    /*
     * @var int
     */
    protected $escposPrinterPort;

    public function __construct(IndexManagerInterface $indexingManager, $escposPrinterIP, $escposPrinterPort)
    {
        $this->indexManager = $indexingManager;
        $this->escposPrinterIP = $escposPrinterIP;
        $this->escposPrinterPort = $escposPrinterPort;
    }

    /**
     * @Route("/accounts", name="accounts")
     */
    public function showIndex(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $repo_account = $this->getDoctrine()->getRepository('AppBundle:Account')->findAll();
        $data['accounts']=$repo_account;

        return $this->render("accounts/index.html.twig", $data);
        
    }

    /**
     * @Route("/accounts/new", name="create_account")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createAccount(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

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
      
        return $this->render(
          'accounts/account.html.twig',
          array(
            'form' => $form->createView(),
            'mode' => 'new_account',
          )
        );
    }

    /**
     * @Route("/accounts/modify/{id}", name="modify_account")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function modifyAccount(Request $request, $id){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        // 1) Récupérer le compte et construire le form
        $repo_account = $this->getDoctrine()->getRepository('AppBundle:Account');
        $account = $repo_account->find($id);
        
        $form = $this->createForm(AccountType::class, $account);

        //$checkAccount = $this->container->get('appbundle.checkaccount');

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /*if ($checkAccount->anneeNotValid($account)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }*/

            // 3) Enregistrer le compte!
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);

            $em->flush();

            $this->indexManager->index($account, $em);

            // ... autres actions

            $this->addFlash('info', 'Le compte de ' .$account->getFirstName(). ' ' .$account->getLastName(). ' a bien été modifié.');

            return $this->redirectToRoute('accounts');
        }

        return $this->render(
            'accounts/account.html.twig',
            array(
                'form' => $form->createView(),
                'mode' => 'modify_account',
                'firstName' => $account->getFirstName(),
                'lastName' => $account->getLastName()
            )
        );
    }

    /**
     * @Route("/accounts/refill/{id}", name="refill_account")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function refillAccount(Request $request, $id){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $account = $this->getDoctrine()->getManager()->getRepository('AppBundle:Account')->find($id);

        $montant = array('message' => 'Montant du rechargement');
        $form = $this->createFormBuilder($montant)
            ->add('montant', MoneyType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
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

            $methode = $request->request->get('methode');

            $montant = $form->getData()['montant'];

            $transaction->setAccount($account);
            $transaction->setUser($user);
            $transaction->setMethode($methode);
            $transaction->setMontant($montant);

            $balance = $account->getBalance();
            $newBalance = $balance + $montant;

            $account->setBalance($newBalance);

            $em->persist($account);

            $em->persist($transaction);

            $em->flush();

            $this->indexManager->index($account, $em);

            $this->addFlash('info',
                $form['montant']->getData().
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

            return $this->redirectToRoute('accounts');
        }

        return $this->render('accounts/refill.html.twig', array(
            'pseudo' => $account->getPseudo(),
            'form' => $form->createView()
        ));
    }
}
