<?php

namespace App\DataFixtures;

use App\Entity\Property;
use App\Entity\PropertyRelation;
use App\Enum\PropertyType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Add properties
        $buildingComplex = new Property();
        $buildingComplex->setName('Building complex');
        $buildingComplex->setType(PropertyType::PROPERTY);
        $manager->persist($buildingComplex);

        $building1 = new Property();
        $building1->setName('Building 1');
        $building1->setType(PropertyType::PROPERTY);
        $manager->persist($building1);

        $building2 = new Property();
        $building2->setName('Building 2');
        $building2->setType(PropertyType::PROPERTY);
        $manager->persist($building2);

        $building3 = new Property();
        $building3->setName('Building 3');
        $building3->setType(PropertyType::PROPERTY);
        $manager->persist($building3);

        // Add parking spaces
        $parkingSpace1 = new Property();
        $parkingSpace1->setName('Parking space 1');
        $parkingSpace1->setType(PropertyType::PARKING_SPACE);
        $manager->persist($parkingSpace1);

        $parkingSpace2 = new Property();
        $parkingSpace2->setName('Parking space 2');
        $parkingSpace2->setType(PropertyType::PARKING_SPACE);
        $manager->persist($parkingSpace2);

        $manager->flush();

        // Add property relations
        $relation1 = new PropertyRelation();
        $relation1->setProperty($building1);
        $relation1->setParent($buildingComplex);
        $manager->persist($relation1);

        $relation2 = new PropertyRelation();
        $relation2->setProperty($building2);
        $relation2->setParent($buildingComplex);
        $manager->persist($relation2);

        $relation3 = new PropertyRelation();
        $relation3->setProperty($building3);
        $relation3->setParent($buildingComplex);
        $manager->persist($relation3);
        
        // Add parking space relations
        $relation4 = new PropertyRelation();
        $relation4->setProperty($parkingSpace1);
        $relation4->setParent($building1);
        $manager->persist($relation4);

        $relation5 = new PropertyRelation();
        $relation5->setProperty($parkingSpace1);
        $relation5->setParent($building2);
        $manager->persist($relation5);

        $relation6 = new PropertyRelation();
        $relation6->setProperty($parkingSpace2);
        $relation6->setParent($building2);
        $manager->persist($relation6);

        $manager->flush();
    }
}
