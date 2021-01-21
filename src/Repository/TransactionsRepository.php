<?php

namespace App\Repository;

use App\Entity\Transactions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transactions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transactions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transactions[]    findAll()
 * @method Transactions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transactions::class);
    }

    /**
     * Pagination tweak collectivised here:
     * https://anil.io/blog/symfony/doctrine/symfony-and-doctrine-pagination-with-twig/
     * All credits to Anil
     *
     * @param int $currentPage
     * @param int $limit
     * @return Paginator
     */
    public function findAllPaginated($currentPage = 1, $limit = 20): Paginator
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.timestamp', 'DESC')
            ->getQuery();

        return $this->paginate($qb, $currentPage, $limit);
    }

    /**
     * @param $dql
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    private function paginate($dql, $page = 1, $limit = 10): Paginator
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

    /**
     * @param $timestamp
     * @return EntityManagerInterface|int|mixed|string
     */
    public function returnTransactionsSince($timestamp)
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->where('t.timestamp > :timestamp')
            ->setParameter('timestamp', $timestamp)
            ->orderBy('t.timestamp', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
