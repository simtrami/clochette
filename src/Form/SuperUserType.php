<?php
// src/AppBundle/Form/SuperUserType.php
namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperUserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, array(
                'label' => "Adresse E-mail",
            ))
            ->add('username', TextType::class, array(
                'label' => "Nom d'utilisateur",
            ))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmer le mot de passe'),
            ))
            ->add('roles', ChoiceType::class, array(
                    'label' => "Poste",
                    'placeholder' => 'Sélectionner le poste',
                    'multiple' => true,
                    'choices' => array(
                        'Président·e' => 'ROLE_ADMIN',
                        'Vice-Président·e' => 'ROLE_ALL',
                        'Trésorier·ère' => 'ROLE_TRESO',
                        'Secrétaire' => 'ROLE_SECRET',
                        'Respo Com' => 'ROLE_COM',
                        'Respo Log' => 'ROLE_STOCK',
                        'Respo Tech' => 'ROLE_SUPER_ADMIN',
                        'Fée' => 'ROLE_VVP',
                        'VVP' => 'ROLE_VVP',
                        'Membre Actif' => 'ROLE_ACTIF',
                        'PC' => 'ROLE_INTRO',
                    ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Users::class,
        ));
    }
}
