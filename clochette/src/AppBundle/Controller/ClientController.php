<?php
/**
 * Created by PhpStorm.
 * User: pierrick
 * Date: 18/05/18
 * Time: 18:19
 */
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $compte=new Comptes();
        $form=$this->createForm(ClientType::class,$compte);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($compte);
            $em->flush();
            return $this->redirectToRoute('login');
        }
        return $this->render('clients/createclient.html.twig',array('form' => $form->createView()));
    }
}