<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\TypeStocks;

class LoadTypeStocks implements FixtureInterface{

    public function load(ObjectManager $manager){
        $names = array(
            'Fût',
            'Bouteille',
            'article'
        );

        foreach ($names as $name){
            $typeStock = new TypeStocks();
            $typeStock->setName($name);

            $manager->persist($typeStock);
        }

        $manager->flush();
    }
}
