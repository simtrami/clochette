<?php

namespace App\DataTableType;

use App\Entity\Account;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

class AccountTableType implements DataTableTypeInterface
{
    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('id', NumberColumn::class, [
                'label' => '#',
            ])
            ->add('firstName', TextColumn::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextColumn::class, [
                'label' => 'Nom',
            ])
            ->add('pseudo', TextColumn::class, [
                'label' => 'Pseudo',
            ])
            ->add('balance', NumberColumn::class, [
                'label' => 'Solde',
                'render' => '%s€',
            ])
            ->add('year', NumberColumn::class, [
                'label' => 'Année',
                'render' => '%sA',
            ])
            ->add('staffName', TextColumn::class, [
                'label' => 'Nom de staff',
                'data' => '---',
            ])
            ->add('isInducted', BoolColumn::class, [
                'label' => 'Intronisé·e',
                'trueValue' => 'Oui',
                'falseValue' => 'Non',
                'nullValue' => 'Non'
            ])
            ->add('actions', TwigColumn::class, [
                'label' => '',
                'className' => 'table-actions',
                'template' => 'accounts/_actions.html.twig',
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Account::class,
            ]);
    }
}