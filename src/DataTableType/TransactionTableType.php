<?php

namespace App\DataTableType;

use App\Entity\Transactions;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORM\SearchCriteriaProvider;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\MapColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;

class TransactionTableType implements DataTableTypeInterface
{
    /**
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('id', NumberColumn::class, [
                'label' => '#',
            ])
            ->add('staff', TextColumn::class, [
                'label' => 'Encaissé par',
                'field' => 'staff.username',
                'data' => '<em>Supprimé·e</em>'
            ])
            ->add('amount', NumberColumn::class, [
                'label' => 'Montant',
                'render' => '%s€',
            ])
            ->add('method', MapColumn::class, [
                'label' => 'Via',
                'map' => [
                    'cash' => 'Liquide',
                    'account' => 'Compte',
                    'pumpkin' => 'Pumpkin',
                    'card' => 'Carte Bleue',
                ],
                'default' => 'Méthode inconnue',
            ])
            ->add('account', TextColumn::class, [
                'label' => 'Compte',
                'field' => 'account.pseudo',
                'searchable' => true,
                'globalSearchable' => true,
                'data' => '---',
            ])
            ->add('timestamp', DateTimeColumn::class, [
                'label' => 'Date et heure',
                'format' => 'd/m/Y H:i:s',
            ])
            ->add('type', TwigColumn::class, [
                'label' => 'Détails',
                'className' => 'table-action',
                'template' => 'transactions/_details.html.twig',
                'orderable' => false,
            ]);

        $account = $options['account'] ?? null;
        // Only query transactions for this account
        // Overrides the registration status criteria
        if ($account) {
            $dataTable->createAdapter(ORMAdapter::class, [
                'entity' => Transactions::class,
                'criteria' => [
                    function (QueryBuilder $qb) use ($account) {
                        $qb
                            ->join('transactions.account', 'a')
                            ->andWhere($qb->expr()->eq('a.pseudo', ':pseudo'))
                            ->setParameter('pseudo', $account);
                    },
                    new SearchCriteriaProvider(),
                ],
            ]);
        } else {
            $registration = $options['registration'] ?? null;
            // Only query transactions registered in a Z report, unregistered or every transactions
            if ($registration === 'registered') {
                $dataTable->createAdapter(ORMAdapter::class, [
                    'entity' => Transactions::class,
                    'criteria' => [
                        function (QueryBuilder $qb) {
                            $qb->andWhere($qb->expr()->isNotNull('transactions.zreport'));
                        },
                        new SearchCriteriaProvider(),
                    ],
                ]);
            } elseif ($registration === 'unregistered') {
                $dataTable->createAdapter(ORMAdapter::class, [
                    'entity' => Transactions::class,
                    'criteria' => [
                        function (QueryBuilder $qb) {
                            $qb->andWhere($qb->expr()->isNull('transactions.zreport'));
                        },
                        new SearchCriteriaProvider(),
                    ],
                ]);
            } else {
                $dataTable->createAdapter(ORMAdapter::class, [
                    'entity' => Transactions::class,
                ]);
            }
        }
    }
}