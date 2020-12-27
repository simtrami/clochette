<?php

namespace App\Repository;

use App\Entity\TypeStocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeStocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeStocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeStocks[]    findAll()
 * @method TypeStocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeStocksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeStocks::class);
    }

    /**
     * Returns the TypeStocks instance with the corresponding type
     *
     * @param string $type
     * @return EntityManagerInterface|int|mixed|string
     * @throws NoResultException If the query returned no result and hydration mode is not HYDRATE_SINGLE_SCALAR.
     * @throws NonUniqueResultException If the query result is not unique.
     */
    public function returnType(string $type)
    {
        $qb = $this->createQueryBuilder('t');

        $qb->where('t.name = :name')
            ->setParameter('name', $type);

        return $qb->getQuery()->getSingleResult();
    }
}
