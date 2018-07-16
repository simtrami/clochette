<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class StockRepository extends \Doctrine\ORM\EntityRepository{

    public function findQuantPos($type){

        $qb = $this->createQueryBuilder('s');

        $qb->where('s.quantite > :limit')
            ->setParameter('limit', 0)
        ->andWhere('s.type = :type')
            ->setParameter('type', $type);
        
        return $qb->getQuery()->getResult();
    }

    public function whereQuantPos(QueryBuilder $qb){

        $qb->andWhere('s.quantite > :limit')
            ->setParameter('limit', 0);
    }

    public function whereType($type, QueryBuilder $qb){

        $qb->andWhere('s.type = :type')
            ->setParameter('type', $type);
    }

    public function isForSale($type){
        $qb = $this->createQueryBuilder('s');

        $qb->where('s.isForSale = :foo')
            ->setParameter('foo', 1);

        $this->whereType($type, $qb);
        
        return $qb->getQuery()->getResult();
    }
}