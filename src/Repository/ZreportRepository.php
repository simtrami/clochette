<?php

namespace App\Repository;

use App\Entity\Zreport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Zreport|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zreport|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zreport[]    findAll()
 * @method Zreport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZreportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zreport::class);
    }

    /**
     * @return EntityManagerInterface|int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function returnLastZTimestamp(){
        $qb = $this->createQueryBuilder('z');

        $qb
            ->select('z.timestamp')
            ->orderBy('z.timestamp', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
