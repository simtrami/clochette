<?php
/**
 * Created by PhpStorm.
 * User: pierrick
 * Date: 18/05/18
 * Time: 18:19
 */
namespace AppBundle\Controller;

use Doctrine\DBAL\Types\BooleanType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Comptes;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Form\ClientType;

class ClientController extends Controller
{
    /**
     * @Route("/clients/create", name="create_client")
     */
    public function createClientAction(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        $compte=new Comptes();
        $form=$this->createForm(ClientType::class,$compte);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }
        return $this->render('clients/createclient.html.twig',array('form' => $form->createView()));
    }

    /**
     * @Route("/clients/modify/{id_client}", name="modify_client")
     */
    public function modifyClientAction(Request $request, $id_client){
        $em=$this->getDoctrine()->getManager();
        $repo_clients=$this->getDoctrine()->getRepository('AppBundle:Comptes');
        $client=$repo_clients->find($id_client);
        $data=[];
        $data['form']=[];
        $data['_token']=$this->get('security.csrf.token_manager')->getToken('form');

        $form=$this->createFormBuilder()
            ->add('nom',TextType::class)
            ->add('prenom',TextType::class)
            ->add('pseudo',TextType::class)
            ->add('nomstaff',TextType::class)
            ->add('annee',IntegerType::class)
            ->add('solde',MoneyType::class)
            ->add('isintro',BooleanType::class)
            ->getForm()
            ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;
            $client->setNom($form_data['nom']);
            $client->setPrenom($form_data['prenom']);
            $client->setPseudo($form_data['pseudo']);
            $client->setNomstaff($form_data['nomstaff']);
            $client->setAnnee($form_data['annee']);
            $client->setSolde($form_data['solde']);
            $client->setIsIntro($form_data['isintro']);
            $em->flush();
            return $this->redirectToRoute('homepage');
        } else{
            $client_data['nom']=$client->getNom();
            $client_data['prenom']=$client->getPrenom();
            $client_data['pseudo']=$client->getPseudo();
            $client_data['nomstaff']=$client->getNomstaff();
            $client_data['annee']=$client->getAnnee();
            $client_data['solde']=$client->getSolde();
            $client_data['isintro']=$client->getIsIntro();

            $data['form']=$client_data;
        }

        return $this->render("clients/modifyclient.html.twig",$data);
    }



    
}