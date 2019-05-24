<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * TreasuryRepository
 */
class TreasuryRepository extends EntityRepository
{
    public function returnLastTreasury(){
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t.id', 't.caisse', 't.coffre')
            ->orderBy('t.id', 'DESC')
            // Not getting the very last one, as it is the one newly created
            ->setFirstResult(1)
            ->setMaxResults(1);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return $e;
        }
    }
}
