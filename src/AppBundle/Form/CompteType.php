<?php
namespace AppBundle\Form;

use AppBundle\Entity\Comptes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class, array(
                'label' => "Nom",
            ))
            ->add('prenom',TextType::class, array(
                'label' => "Prénom",
            ))
            ->add('pseudo',TextType::class, array(
                'label' => "Pseudo",
            ))
            ->add('solde',MoneyType::class, array(
                'label' => "Solde",
            ))
            ->add('annee',IntegerType::class, array(
                'label' => "Année",
            ))
            ->add('is_intro', ChoiceType::class, array(
                'label' => "Intronisé",
                'placeholder' => "Intronisé ?",
                'choices' => array(
                    'OUI' => true,
                    'NON' => false,
                ),
            ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
          'data_class' => Comptes::class,
        ));
    }
}