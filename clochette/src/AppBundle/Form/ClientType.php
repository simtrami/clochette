<?php
/**
 * Created by PhpStorm.
 * User: pierrick
 * Date: 18/05/18
 * Time: 18:10
 */

namespace AppBundle\Form;

use AppBundle\Entity\Comptes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class)
            ->add('prenom',TextType::class)
            ->add('login',TextType::class)
            ->add('nomstaff',TextType::class)
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('data_class' => Comptes::class,));
    }
}