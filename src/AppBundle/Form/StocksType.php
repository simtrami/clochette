<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class StocksType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prixAchat', MoneyType::class, array(
                'label' => "Prix à l'achat",
            ))
            ->add('prixVente', MoneyType::class, array(
                'label' => "Prix à la vente",
            ))
            ->add('quantite', IntegerType::class, array(
                'label' => "Quantité",
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $article = $event->getData();
            $form = $event->getForm();
    
            // Vérifie si l'objet Article est "nouveau"
            // Si aucune donnée n'est passée au form, alors elle vaut "null".
            // Ceci doit être considéré comme un nouvel Article :
            if (!$article || null === $article->getId()) {
                $form
                    ->add('nom', TextType::class, array(
                        'label' => "Nom de l'article",
                    ))
                    ->add('type', EntityType::class, array(
                        'label' => "Type d'article",
                        'choice_label' => 'name',
                        'placeholder' => "Selectionner le type d'article",
                        'class' => 'AppBundle:TypeStocks'
                    ))
                    ->add('volume', NumberType::class, array(
                        'label' => "Volume à l'unité",
                        'required' => false,
                    ));
            } else if ($article->getType() != "Nourriture ou autre") {
                $form
                    ->add('volume', NumberType::class, array(
                        'label' => "Volume à l'unité",
                        'required' => false,
                    ));
            }
        });
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Stocks'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_stocks';
    }


}
