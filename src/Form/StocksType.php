<?php

namespace App\Form;

use App\Entity\Stocks;
use App\Entity\TypeStocks;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cost', MoneyType::class, array(
                'label' => "Prix à l'achat (TTC+accise, hors consigne)",
            ))
            ->add('sellingPrice', MoneyType::class, array(
                'label' => "Prix à la vente (/unité)",
            ))
            ->add('quantity', IntegerType::class, array(
                'label' => "Quantité",
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $article = $event->getData();
            $form = $event->getForm();
    
            // Vérifie si l'objet Article est "nouveau"
            // Si aucune donnée n'est passée au form, alors elle vaut "null".
            // Ceci doit être considéré comme un nouvel Article :
            if (!$article || is_null($article->getId())) {
                $form
                    ->add('name', TextType::class, array(
                        'label' => "Nom de l'article",
                    ))
                    ->add('type', EntityType::class, array(
                        'label' => "Type d'article",
                        'choice_label' => 'name',
                        'placeholder' => "Selectionner le type d'article",
                        'class' => TypeStocks::class
                    ))
                    ->add('volume', NumberType::class, array(
                        'label' => "Volume à l'unité (L)",
                        'required' => false,
                    ));
            } else if ($article->getType() !== "Nourriture ou autre") {
                $form
                    ->add('volume', NumberType::class, array(
                        'label' => "Volume à l'unité (L)",
                        'required' => false,
                    ));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Stocks::class
        ));
    }
}
