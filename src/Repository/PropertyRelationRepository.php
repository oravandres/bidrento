<?php

namespace App\Repository;

use App\Entity\PropertyRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PropertyRelation>
 */
class PropertyRelationRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry The ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyRelation::class);
    }
}
