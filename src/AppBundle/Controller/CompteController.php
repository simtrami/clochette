<?php
// src/AppBundle/Controller/ComptesController.php
namespace AppBundle\Controller;

use Algolia\SearchBundle\IndexManagerInterface;
use AppBundle\Entity\Comptes;
use AppBundle\Form\CompteType;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use AppBundle\Entity\Transactions;

class CompteController extends Controller {

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
     * @Route("/comptes", name="comptes")
     */
    public function showIndex(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes')->findAll();
        $data['comptes']=$repo_comptes;

        return $this->render("comptes/index.html.twig", $data);
        
    }

    /**
     * @Route("/comptes/nouveau", name="ajout_compte")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createCompte(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $compte=new Comptes();
        $form=$this->createForm(CompteType::class, $compte);

        $form->handleRequest($request);

        //$checkAccount = $this->container->get('appbundle.checkaccount');

        if($form->isSubmitted() && $form->isValid()) {
            /*if ($checkAccount->anneeNotValid($compte)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }*/

            /*if (!$checkAccount->namesValid($compte)){
                throw new \Exception('Les nom, prénom et pseudo ne peuvent contenir que des lettres.');
            }*/

            $em = $this->getDoctrine()->getManager();
            $em->persist($compte);

            $em->flush();

            $this->indexManager->index($compte, $em);

            $this->addFlash('info', 'Un nouveau compte a été créé.');
        
            return $this->redirectToRoute('ajout_compte');
        }
      
        return $this->render(
          'comptes/compte.html.twig',
          array(
            'form' => $form->createView(),
            'mode' => 'new_account',
          )
        );
    }

    /**
     * @Route("/comptes/modifier/{id}", name="modif_compte")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function modifyCompte(Request $request, $id){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        // 1) Récupérer le compte et construire le form
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        $compte = $repo_comptes->find($id);
        
        $form = $this->createForm(CompteType::class, $compte);

        //$checkAccount = $this->container->get('appbundle.checkaccount');

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /*if ($checkAccount->anneeNotValid($compte)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }*/

            // 3) Enregistrer le compte!
            $em = $this->getDoctrine()->getManager();
            $em->persist($compte);

            $em->flush();

            $this->indexManager->index($compte, $em);

            // ... autres actions

            $this->addFlash('info', 'Le compte de ' .$compte->getPrenom(). ' ' .$compte->getNom(). ' a bien été modifié.');

            return $this->redirectToRoute('comptes');
        }

        return $this->render(
            'comptes/compte.html.twig',
            array(
                'form' => $form->createView(),
                'mode' => 'modify_account',
                'prenom' => $compte->getPrenom(),
                'nom' => $compte->getNom()
            )
        );
    }

    /**
     * @Route("/comptes/recharger/{id}", name="recharger_compte")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function rechargeCompte(Request $request, $id){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $compte = $this->getDoctrine()->getManager()->getRepository('AppBundle:Comptes')->find($id);
        $session = $request->getSession();

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
            $repo_comptes = $em->getRepository('AppBundle:Comptes');
            $repo_users = $em->getRepository('AppBundle:Users');

            $compte = $repo_comptes->find($id);

            $user = $repo_users->find($this->getUser()->getId());

            $methode = $request->request->get('methode');

            $montant = $form->getData()['montant'];

            $transaction->setCompte($compte);
            $transaction->setUser($user);
            $transaction->setMethode($methode);
            $transaction->setMontant($montant);

            $solde = $compte->getSolde();
            $newSolde = $solde + $montant;

            $compte->setSolde($newSolde);

            $em->persist($compte);

            $em->persist($transaction);

            $em->flush();

            $this->indexManager->index($compte, $em);

            $this->addFlash('info',
                $form['montant']->getData().
                '€ ont été ajoutés au compte de '.$compte->getPrenom(). 
                ' '.$compte->getNom().
                '. Son solde est désormais de '.$newSolde.'€.'
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

            return $this->redirectToRoute('comptes');
        }

        return $this->render('comptes/recharger.html.twig', array(
            'pseudo' => $compte->getPseudo(),
            'form' => $form->createView()
        ));
    }
}
