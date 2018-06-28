<?php
// src/AppBundle/Controller/ComptesController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Comptes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use AppBundle\Form\CompteType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;



class CompteController extends Controller {

    /**
     * @Route("/comptes", name="comptes")
     */
    public function showIndex(){
        $em = $this->getDoctrine()->getManager();
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes')->findAll();
        $data['comptes']=$repo_comptes;

        return $this->render("comptes/comptes.html.twig", $data);
        
    }

    /**
     * @Route("/comptes/create", name="create_compte")
     */
    public function createClientAction(Request $request){
        $compte=new Comptes();
        $form=$this->createForm(CompteType::class,$compte);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();
            return $this->redirectToRoute('comptes');
        }
        return $this->render('comptes/createcompte.html.twig',array('form' => $form->createView()));
    }

    /**
     * @Route("/comptes/modify/{idcompte}", name="modify_compte")
     */

    public function modifyCompte(Request $request, $idcompte){

        // 1) Récupérer le compte et construire le form
        $repo_comptes = $this->getDoctrine()->getRepository('AppBundle:Comptes');
        $compte = $repo_comptes->find($idcompte);


        // Récupération du statut d'intronisation et traduction
        switch($compte->getIsIntro()) {
            case "1":
                $is_intro = "OUI";
                break;
            case "0":
                $is_intro = "NON";
                break;
        }
        
        $form = $this->createForm(CompteType::class, $compte);

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Enregistrer le compte!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($compte);
            $entityManager->flush();

            // ... autres actions

            return $this->redirectToRoute('comptes');
        }

        return $this->render('comptes/createcompte.html.twig');
    }    
    
}