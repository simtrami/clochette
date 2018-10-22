<?php
namespace AppBundle\Form;

use AppBundle\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('firstName',TextType::class, array(
                'label' => "Prénom",
            ))
            ->add('lastName',TextType::class, array(
                'label' => "Nom",
            ))
            ->add('pseudo',TextType::class, array(
                'label' => "Pseudo",
            ))
            ->add('year',IntegerType::class, array(
                'label' => "Année",
            ))
            ->add('isInducted', ChoiceType::class, array(
                'label' => "Intronisé",
                'placeholder' => "Intronisé ?",
                'choices' => array(
                    'OUI' => true,
                    'NON' => false,
                ),
            ))
            
            ;
            
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $compte = $event->getData();
                $form = $event->getForm();

                if (!(!$compte || null === $compte->getId())) {
                    $form
                        ->add('staffName', TextType::class, array(
                        'label' => 'Nom de Staff', 
                        'required' => false
                        ));
                } else {
                    $form
                        ->add('balance',MoneyType::class, array(
                        'label' => "Solde",
                    ));
                }
            });
    
        }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
          'data_class' => Account::class,
        ));
    }
}
