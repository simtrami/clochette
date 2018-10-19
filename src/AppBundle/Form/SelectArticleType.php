<?php
// Aller voir https://stackoverflow.com/questions/14638794/build-a-form-having-a-checkbox-for-each-entity-in-a-doctrine-collection
namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Stocks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SelectArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isForSale', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
            ))
            ->add('nom', TextType::class, array(
                'disabled' => true,
            ))
            ->add('prixVente', TextType::class, array(
                'disabled' =>  true,
            ))
            ->add('quantite', TextType::class, array(
                'disabled' => true,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Stocks::class,
        ));
    }
}
