<?php

namespace App\Form;

use App\Entity\Treasury;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TreasuryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cashRegister', MoneyType::class, array(
                'label' => 'Contenu de la caisse',
                'required' => true,
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $treasury = $event->getData();
            $form = $event->getForm();

            if ($treasury && !is_null($treasury->getId())) {
                $form
                    ->add('safe', MoneyType::class, array(
                        'label' => 'Contenu du coffre',
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
            'data_class' => Treasury::class
        ));
    }


}
