<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller{

    /**
     * @Route("/users", name="users")
    **/

    public function indexAction(){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }


        $repo_users = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users');

        $listUsers = $repo_users->findAll();

        return $this->render('users/index.html.twig', array(
            'users' => $listUsers
        ));
    }

    /**
     * @Route("/users/modify/{id}", name="users_modify")
    **/
    public function modifyAction(Request $request, $id){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $repo_users = $em->getRepository('AppBundle:Users');

        $user = $repo_users->find($id);

        $form = $this->createForm(UserType::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('users');
        }

        return $this->render('users/modify.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

        /**
     * @Route("/users/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $session = $request->getSession();
      
        // 1) build the form
        $user = new Users();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $session->getFlashbag()->add('info', $user->getusername().', votre compte a bien été créé. Connectez-vous dès maintenant.');

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'users/register.html.twig',
            array('form' => $form->createView())
        );
    }
}