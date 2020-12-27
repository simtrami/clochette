<?php

namespace App\Repository;

use App\Entity\Treasury;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Treasury|null find($id, $lockMode = null, $lockVersion = null)
 * @method Treasury|null findOneBy(array $criteria, array $orderBy = null)
 * @method Treasury[]    findAll()
 * @method Treasury[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreasuryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Treasury::class);
    }

    /**
     * @return EntityManagerInterface|int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function returnLastTreasury()
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t.id', 't.cashRegister', 't.safe')
            ->orderBy('t.id', 'DESC')
            // Not getting the very last one, as it is the one newly created
            ->setFirstResult(1)
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return EntityManagerInterface|int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function latest()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->orderBy('t.id', 'DESC');
        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }
}
