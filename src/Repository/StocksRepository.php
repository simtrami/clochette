<?php

namespace App\Repository;

use App\Entity\Stocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Stocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stocks[]    findAll()
 * @method Stocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StocksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stocks::class);
    }

    /**
     * @param $type
     * @return EntityManagerInterface|int|mixed|string
     */
    public function loadQuantityNotNullByType($type)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where('s.quantity > :limit')
            ->setParameter('limit', 0)
            ->andWhere('s.type = :type')
            ->setParameter('type', $type);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     */
    public function whereQuantityNotNull(QueryBuilder $qb): void
    {
        $qb->andWhere('s.quantity > 0');
    }

    /**
     * @param $type
     * @param QueryBuilder $qb
     */
    public function whereType($type, QueryBuilder $qb): void
    {
        $qb->andWhere('s.type = :type')
            ->setParameter('type', $type);
    }

    /**
     * @param $type
     * @return EntityManagerInterface|int|mixed|string
     */
    public function loadStocksForSaleByType($type){
        $qb = $this->createQueryBuilder('s');

        $qb->where('s.isForSale = 1');

        $this->whereType($type, $qb);

        return $qb->getQuery()->getResult();
    }
}
