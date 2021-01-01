<?php

namespace App\DataFixtures;

use App\Entity\TypeStocks;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeStocksFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $barrel = $this->instantiateTypeStock('Fût');
        $manager->persist($barrel);
        $bottle = $this->instantiateTypeStock('Bouteille');
        $manager->persist($bottle);
        $other = $this->instantiateTypeStock('Nourriture ou autre');
        $manager->persist($other);

        $manager->flush();
    }

    private function instantiateTypeStock(string $name): TypeStocks
    {
        $type = new TypeStocks();
        $type->setName($name);
        return $type;
    }
}
