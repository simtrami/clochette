<?php
// src/AppBundle/Controller/ComptesController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Comptes;
use AppBundle\Form\CompteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Types\BooleanType;

class CompteController extends Controller {

    /**
     * @Route("/comptes", name="comptes")
     */
    public function showIndex(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes')->findAll();
        $data['comptes']=$repo_comptes;

        return $this->render("comptes/index.html.twig", $data);
        
    }

    /**
     * @Route("/comptes/create", name="create_compte")
     */
    public function createCompte(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

      
        $compte=new Comptes();
        $form=$this->createForm(CompteType::class, $compte);

        $form->handleRequest($request);

        $checkAccount = $this->container->get('appbundle.checkaccount');

        if($form->isSubmitted() && $form->isValid()) {
            /*if ($checkAccount->anneeNotValid($compte)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }*/

            if (!$checkAccount->namesValid($compte)){
                throw new \Exception('Les nom, prénom et pseudo ne peuvent contenir que des lettres.');
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();

            $request->getSession()->getFlashbag()->add('info', 'Un nouveau compte a été créé.');
        
            return $this->redirectToRoute('comptes');
        }
      
        return $this->render(
          'comptes/create.html.twig',
          array(
            'form' => $form->createView(),
            'mode' => 'new_account',
          )
        );
    }

    /**
     * @Route("/comptes/modify/{id}", name="modify_compte")
     */

    public function modifyCompte(Request $request, $id){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        // 1) Récupérer le compte et construire le form
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        $compte = $repo_comptes->find($id);
        
        $form = $this->createForm(CompteType::class, $compte);

        $checkAccount = $this->container->get('appbundle.checkaccount');

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($checkAccount->anneeNotValid($compte)){
                throw new \Exception('Une année doit au moins être égale à 1');
            }

            // 3) Enregistrer le compte!
            $em = $this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();

            // ... autres actions

            $request->getSession()->getFlashbag()->add('info', 'Le compte de ' .$compte->getPrenom(). ' ' .$compte->getNom(). ' a bien été modifié.');

            return $this->redirectToRoute('comptes');
        }

        return $this->render(
            'comptes/create.html.twig',
            array(
                'form' => $form->createView(),
                'mode' => 'modify_account',
                'prenom' => $compte->getPrenom(),
                'nom' => $compte->getNom()
            )
        );
    }    
    
}