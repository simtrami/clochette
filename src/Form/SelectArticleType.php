<?php
// Aller voir https://stackoverflow.com/questions/14638794/build-a-form-having-a-checkbox-for-each-entity-in-a-doctrine-collection
namespace App\Form;

use Doctrine\ORM\EntityRepository;
use App\Entity\Stocks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SelectArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isForSale', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
            ))
            ->add('name', TextType::class, array(
                'disabled' => true,
            ))
            ->add('sellingPrice', TextType::class, array(
                'disabled' =>  true,
            ))
            ->add('quantity', TextType::class, array(
                'disabled' => true,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Stocks::class,
        ));
    }
}
