<?php

namespace App\Repository;

use App\Entity\DetailsTransactions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DetailsTransactions|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailsTransactions|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailsTransactions[]    findAll()
 * @method DetailsTransactions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailsTransactionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailsTransactions::class);
    }

    // /**
    //  * @return DetailsTransactions[] Returns an array of DetailsTransactions objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DetailsTransactions
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
