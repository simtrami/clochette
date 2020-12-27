<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\SuperUserType;
use App\Form\UserType;
use Exception;
use Swift_Image;
use Swift_Message;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends BasicController
{
    protected $sendingAddress;

    public function __construct()
    {
        $this->sendingAddress = getenv('MAILER_USER');
    }

    /**
     * @Route("/users", name="users")
    **/
    public function indexAction(): Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();

        $users = $this->getDoctrine()->getManager()->getRepository(Users::class)->findAll();
        $this->data['users'] = $users;

        return $this->render('users/index.html.twig', $this->data);
    }

    /**
     * @Route("/users/{id}/edit", name="modify_user", requirements={"id"="\d+"})
     * @param Request $request
     * @param $user
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function modifyAction(Request $request, Users $user, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->getModes();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') && $this->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas modifier un compte qui n'est pas le votre");
        }

        $em = $this->getDoctrine()->getManager();

//        $user = $em->getRepository(Users::class)->find($id);
        $infos = array(
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
        );

        $form = $this->createFormBuilder($infos)
            ->add('email', EmailType::class, array(
                'label' => 'Adresse E-mail'
            ))
            ->add('username', TextType::class, array(
                'label' => "Nom d'utilisateur"
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Mettre à jour'
            ))
            ->getForm()
        ;

        $form_pw = $this->createFormBuilder()
            ->add('password', RepeatedType::class, array(
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
            }

            $this->addFlash('info', $user->getUsername(). ', votre compte a bien été modifié. Reconnectez-vous.');

            return $this->redirectToRoute('homepage');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_pw'] = $form_pw->createView();
        $this->data['user'] = $user;

        return $this->render('users/modify.html.twig', $this->data);
    }

    /**
     * @Route("/users/create", name="add_user")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param MailerInterface $mailer
     * @return RedirectResponse|Response
     * @throws TransportExceptionInterface
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->getModes();

        $user = new Users();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form = $this->createForm(SuperUserType::class, $user);
        } else {
            $form = $this->createForm(UserType::class, $user);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // Génération du mail
            $this->data['username'] = $user->getUsername();
            $this->data['roles'] = $user->getRoles();
            // TODO: Write it in Markdown
            $message = (new TemplatedEmail())
                ->from($this->sendingAddress)
                ->to($user->getEmail())
                ->subject('Confirmation de création du compte utilisateur')
                ->htmlTemplate('emails/newUser.html.twig')
                ->context($this->data);
            $mailer->send($message);

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
     * @param $user
     * @return RedirectResponse
     */
    public function disableAction(Users $user): RedirectResponse
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

//        $user = $this->getDoctrine()->getRepository(Users::class)->find($users);

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
