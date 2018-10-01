<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class TypeStocksRepository extends EntityRepository
{
    /* Cette méthode permet de retourner l'instance de TypeStocks $type correspondant à la chaine de caractère $type */
    public function returnType($type){
        $qb = $this->createQueryBuilder('t');

        $qb->where('t.name = :name')
            ->setParameter('name', $type);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
    }
}
