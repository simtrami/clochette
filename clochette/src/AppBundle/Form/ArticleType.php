<?php
// src/AppBundle/Form/ArticleType.php
namespace AppBundle\Form;

use AppBundle\Entity\Stocks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ArticleType extends AbstractType
{
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
            ->add('volume', NumberType::class, array(
                'label' => "Volume à l'unité",
                'required'      => false,
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $article = $event->getData();
            $form = $event->getForm();
    
            // Vérifie si l'objet Article est "nouveau"
            // Si aucune donnée n'est passée au form, alors elle vaut "null".
            // Ceci doit être considéré comme un nouvel Article :
            if (!$article || null === $article->getIdarticle()) {
                $form
                    ->add('nom', TextType::class, array(
                        'label' => "Nom de l'article",
                    ))
                    ->add('type', ChoiceType::class, array(
                        'label' => "Type d'article",
                        'placeholder' => "Selectionner le type d'article",
                        'choices' => array(
                            'Fût' => 'draft',
                            'Bouteille' => 'bottle',
                            'Nourriture ou autre' => 'article',
                        ),
                    ))
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Stocks::class,
        ));
    }
}