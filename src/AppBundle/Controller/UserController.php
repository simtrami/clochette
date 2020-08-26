<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Users;
use AppBundle\Form\SuperUserType;
use AppBundle\Form\UserType;
use Exception;
use Swift_Image;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends BasicController
{
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $repo_users = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users');

        $users = $repo_users->findAll();

        $this->data['users'] = $users;

        return $this->render('users/index.html.twig', $this->data);
    }

    /**
     * @Route("/users/{id}/modify", name="modify_user", requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function modifyAction(Request $request, $id, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        if ($this->getUser()->getId() != $id && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            throw $this->createAccessDeniedException("Vous ne pouvez pas modifier un compte qui n'est pas le votre");
        }

        $this->getModes();

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

        if ($request->isMethod('POST') && ($form->handleRequest($request)->isValid() || $form_pw->handleRequest($request)->isValid())){
            if ($form->isValid()){
                $user->setEmail($form['email']->getData());
                $user->setUsername($form['username']->getData());
            }
            if ($form_pw->isValid()){
                $password = $passwordEncoder->encodePassword($user, $form_pw['plainPassword']->getData());
                $user->setPassword($password);
            }

            try {
                $em->persist($user);
                $em->flush();
            } catch (Exception $e) {
                $this->addFlash('error', 'La modification du compte a échoué.');
                return $this->redirectToRoute('homepage');
            }

            if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
                $this->addFlash('info', 'Le compte "' . $user->getUsername() . '" a bien été modifié');

                return $this->redirectToRoute('users');
            } else{
                $this->addFlash('info', $user->getUsername(). ', votre compte a bien été modifié. Reconnectez-vous.');

                return $this->redirectToRoute('homepage');
            }
        }

        $this->data['form'] = $form->createView();
        $this->data['form_pw'] = $form_pw->createView();
        $this->data['user'] = $user;

        return $this->render('users/modify.html.twig', $this->data);
    }

    /**
     * @Route("/users/new", name="add_user")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') && $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            // 1) build the form
            $user = new Users();
            $form = $this->createForm(SuperUserType::class, $user);
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            // 1) build the form
            $user = new Users();
            $form = $this->createForm(UserType::class, $user);
        } else {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

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
            $message = (new Swift_Message('Confirmation de création du compte utilisateur'))
                ->setFrom($this->sendingAddress)
                ->setTo($user->getEmail());
            $this->data['logo'] = $message->embed(Swift_Image::fromPath('images/logo.ico'));
            $this->data['username'] = $user->getUsername();
            $this->data['roles'] = $user->getRoles();
            $message->setBody(
                $this->renderView(
                    'emails/newUser.html.twig',
                    $this->data
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

        $this->data['form'] = $form->createView();

        return $this->render(
            'users/add.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/users/{id}/toggle", name="toggle_user", requirements={"id"="\d+"})
     * @param $id
     * @return RedirectResponse
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
