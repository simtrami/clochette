<?php
namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, array(
                'label' => "Prénom",
            ))
            ->add('lastName', TextType::class, array(
                'label' => "Nom",
            ))
            ->add('pseudo',TextType::class, array(
                'label' => "Surnom",
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
            ));
            
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $account = $event->getData();
                $form = $event->getForm();

                if ($account && null !== $account->getId()) {
                    $form->add('staffName', TextType::class, array(
                        'label' => 'Nom de Staff',
                        'required' => false
                    ));
                } else {
                    $form->add('balance', MoneyType::class, array(
                        'label' => "Solde",
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
          'data_class' => Account::class,
        ));
    }
}
