<?php
namespace App\Form;

use App\Entity\SellsManagement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SellsManagementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => SellsManagement::class,
        ));
    }
}
