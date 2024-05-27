<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\PropertyRelation;
use App\Enum\PropertyStatus;
use App\Enum\PropertyType;
use App\Repository\PropertyRelationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PropertyRelationService
{
    private $propertyRelationRepository;
    private $entityManager;

    public function __construct(PropertyRelationRepository $propertyRelationRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->propertyRelationRepository = $propertyRelationRepository;
        $this->entityManager = $entityManager;
    }

    public function getAllActiveRelations(): array
    {
        return $this->propertyRelationRepository->findBy(['status' => PropertyStatus::ACTIVE->value]);
    }

    public function createRelation(Property $property, Property $parent): void
    {
        $relation = new PropertyRelation();
        $relation->setProperty($property);
        $relation->setParent($parent);

        $this->entityManager->persist($relation);
        $this->entityManager->flush();
    }

    public function softDeleteRelationsForProperty(Property $property): void
    {
        $relations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        foreach ($relations as $relation) {
            $relation->setStatus(PropertyStatus::DELETED);
        }

        $this->entityManager->flush();
    }

    public function findBy(array $criteria): array
    {
        return $this->propertyRelationRepository->findBy($criteria);
    }

    public function getParents(Property $property): array
    {
        $parentRelations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        $parents = [];
        foreach ($parentRelations as $relation) {
            $parents[] = $relation->getParent();
        }
        return $parents;
    }

    public function getSiblings(Property $property): array
    {
        $siblings = [];
        $parentRelations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        foreach ($parentRelations as $relation) {
            $siblingRelations = $this->propertyRelationRepository->findBy(['parent' => $relation->getParent(), 'status' => PropertyStatus::ACTIVE->value]);
            foreach ($siblingRelations as $siblingRelation) {
                if ($siblingRelation->getProperty() !== $property) {
                    $siblings[] = $siblingRelation->getProperty();
                }
            }
        }
        return $siblings;
    }

    public function getChildren(Property $property): array
    {
        $childRelations = $this->propertyRelationRepository->findBy(['parent' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        $children = [];
        foreach ($childRelations as $relation) {
            $children[] = $relation->getProperty();
        }
        return $children;
    }

    public function buildPropertyTree(array $properties, array $relations): array
    {
        $propertyMap = $this->initializePropertyMap($properties);
        $this->buildParentChildRelationships($propertyMap, $relations);
        return $this->extractRootNodes($propertyMap, $relations);
    }

    private function initializePropertyMap(array $properties): array
    {
        $propertyMap = [];
        foreach ($properties as $property) {
            $propertyMap[$property->getId()] = [
                'id' => $property->getId(),
                'name' => $property->getName(),
                'type' => $property->getType()->toString(),
                'created' => $property->getCreated(),
                'modified' => $property->getModified(),
                'status' => $property->getStatus()->toString(),
                'children' => []
            ];
        }
        return $propertyMap;
    }

    private function buildParentChildRelationships(array &$propertyMap, array $relations): void
    {
        foreach ($relations as $relation) {
            $parentId = $relation->getParent() ? $relation->getParent()->getId() : null;
            $childId = $relation->getProperty()->getId();

            if ($parentId !== null && isset($propertyMap[$parentId]) && isset($propertyMap[$childId])) {
                $propertyMap[$parentId]['children'][] = &$propertyMap[$childId];
            }
        }
    }

    private function extractRootNodes(array $propertyMap, array $relations): array
    {
        $tree = [];
        foreach ($propertyMap as $propertyId => $property) {
            if ($this->isRootNode($propertyId, $relations)) {
                $tree[] = $property;
            }
        }
        return $tree;
    }

    private function isRootNode(int $propertyId, array $relations): bool
    {
        foreach ($relations as $relation) {
            if ($relation->getProperty()->getId() === $propertyId && $relation->getParent() !== null) {
                return false;
            }
        }
        return true;
    }

    public function validateAndCreateRelation(Property $property, Property $parent, PropertyType $type): void
    {
        $this->validateRelation($property, $parent, $type);
        $this->createRelation($property, $parent);
    }

    private function validateRelation(Property $property, Property $parent, PropertyType $type): void
    {
        // Check if a regular property already has a parent relation
        if ($type === PropertyType::PROPERTY && $this->propertyRelationRepository->findOneBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value])) {
            throw new \Exception('A regular property cannot have multiple parents');
        }

        // Check if the relation already exists
        $existingRelation = $this->propertyRelationRepository->findOneBy(['property' => $property, 'parent' => $parent, 'status' => PropertyStatus::ACTIVE->value]);
        if ($existingRelation) {
            throw new \Exception('This property relation already exists');
        }

        // Check if property is being added to itself
        if ($property->getId() === $parent->getId()) {
            throw new \Exception('A property cannot be added to itself');
        }
    }

    public function hasActiveChildren(Property $property): bool
    {
        return $this->propertyRelationRepository->findOneBy(['parent' => $property, 'status' => PropertyStatus::ACTIVE->value]) !== null;
    }

    public function softDeleteRelations(Property $property): void
    {
        $relations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        foreach ($relations as $relation) {
            $relation->setStatus(PropertyStatus::DELETED);
        }
        
        $this->entityManager->flush();
    }
}
