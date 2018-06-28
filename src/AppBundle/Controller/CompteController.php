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
        $form=$this->createForm(CompteType::class,$compte);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
        
            $em = $this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();
          
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
     * @Route("/comptes/modify/{idcompte}", name="modify_compte")
     */

    public function modifyCompte(Request $request, $idcompte){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        // 1) Récupérer le compte et construire le form
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        $compte = $repo_comptes->find($idcompte);
        
        $form = $this->createForm(CompteType::class, $compte);

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Enregistrer le compte!
            $em = $this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();

            // ... autres actions

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