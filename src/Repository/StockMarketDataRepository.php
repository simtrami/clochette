<?php

namespace App\Repository;

use App\Entity\StockMarketData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StockMarketData|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockMarketData|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockMarketData[]    findAll()
 * @method StockMarketData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockMarketDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMarketData::class);
    }

    // /**
    //  * @return StockMarketData[] Returns an array of StockMarketData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StockMarketData
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
