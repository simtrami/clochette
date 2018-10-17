<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Users;
use Swift_Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\SuperUserType;
use AppBundle\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserController extends Controller{
    protected $sendingAddress;

    public function __construct($sendingAddress)
    {
        $this->sendingAddress = $sendingAddress;
    }

    /**
     * @Route("/users", name="users")
    **/

    public function indexAction()
    {
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
     * @Route("/users/modify/{id}", name="modify_user")
     * @param Request $request
     * @param $id
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifyAction(Request $request, $id, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->getUser()->getId() != $id && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier un compte qui n\'est pas le votre');
        }

        $em = $this->getDoctrine()->getManager();
        $repo_users = $em->getRepository('AppBundle:Users');

        $user = $repo_users->find($id);
        $infos = array(
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
        );

        $infos_pw = array('plainPassword' => $user->getPlainPassword());

        $form = $this->createFormBuilder($infos)
            ->add('email', EmailType::class, array(
                'label' => 'Adresse E-mail'
            ))
            ->add('username', TextType::class, array(
                'label' => 'Nom d\'utilisateur'
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Mettre à jour'
            ))
            ->getForm()
        ;

        $form_pw = $this->createFormBuilder($infos_pw)
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmer le mot de passe')
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Modifier'
            ))
            ->getForm()
        ;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()){

            $user->setEmail($form['email']->getData());
            $user->setUsername($form['username']->getData());

            $em->persist($user);
            $em->flush();

            if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){

                $this->addFlash('info', 'Le compte "' . $user->getUsername() . '" a bien été modifié');

                return $this->redirectToRoute('users');
            }
            else{

                $this->addFlash('info', $user->getUsername(). ', votre compte a bien été modifié');
                
                return $this->redirectToRoute('homepage');
            }
        }

        if ($request->isMethod('POST') && $form_pw->handleRequest($request)->isValid()){

            $password = $passwordEncoder->encodePassword($user, $form_pw['plainPassword']->getData());
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('users');
        }

        return $this->render('users/modify.html.twig', array(
            'form' => $form->createView(),
            'form_pw' => $form_pw->createView(),
            'user' => $user
        ));
    }

    /**
     * @Route("/users/add", name="add_user")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            // 1) build the form
            $user = new Users();
            $form = $this->createForm(SuperUserType::class, $user);
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            // 1) build the form
            $user = new Users();
            $form = $this->createForm(UserType::class, $user);
        } else {
            throw $this->createAccessDeniedException();
        }

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

            // Génération du mail
            $message = (new \Swift_Message('Confirmation de création du compte utilisateur'))
                ->setFrom($this->sendingAddress)
                ->setTo($user->getEmail());
            $data['logo'] = $message->embed(Swift_Image::fromPath('images/logo.ico'));
            $data['username'] = $user->getUsername();
            $data['roles'] = $user->getRoles();
            $message->setBody(
                $this->renderView(
                    'emails/newUser.html.twig',
                    $data
                ),
                'text/html'
            )/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
            ;
            $this->get('mailer')->send($message);

            $this->addFlash('info', $user->getusername().', votre compte a bien été créé. Connectez-vous dès maintenant.');

            return $this->redirectToRoute('add_user');
        }

        return $this->render(
            'users/add.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/users/toggle/{id}", name="toggle_user")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function disableAction($id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getDoctrine()->getRepository(Users::class)->find($id);

        if ($user->getIsActive()) {
            $user->setIsActive(false);
        } else {
            $user->setIsActive(true);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('users');
    }
}
