<?php
namespace App\Form;

use App\Entity\SellsManagement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SellsManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('drafts', CollectionType::class, array(
                'entry_type' => SelectArticleType::class,
                'entry_options' => array('label' => false),
            ))
            ->add('bottles', CollectionType::class, array(
                'entry_type' => SelectArticleType::class,
                'entry_options' => array('label' => false),
            ))
            ->add('articles', CollectionType::class, array(
                'entry_type' => SelectArticleType::class,
                'entry_options' => array('label' => false),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SellsManagement::class,
        ));
    }
}
