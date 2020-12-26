<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * ZreportRepository
 */
class ZreportRepository extends \Doctrine\ORM\EntityRepository
{
    public function returnLastZTimestamp(){
        $qb = $this->createQueryBuilder('z');

        $qb
            ->select('z.timestamp')
            ->orderBy('z.timestamp', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }
    }
}
