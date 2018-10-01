<?php

namespace AppBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;

/**
 * TreasuryRepository
 */
class TreasuryRepository extends \Doctrine\ORM\EntityRepository
{
    public function returnLastTreasury(){
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t.id', 't.coffre')
            ->orderBy('t.id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }
    }
}
