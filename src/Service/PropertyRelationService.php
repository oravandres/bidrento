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

    /**
     * @param PropertyRelationRepository $propertyRelationRepository The repository for property relations.
     * @param EntityManagerInterface $entityManager The entity manager.
     * @param ValidatorInterface $validator The validator.
     */
    public function __construct(PropertyRelationRepository $propertyRelationRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->propertyRelationRepository = $propertyRelationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves all active property relations.
     *
     * @return PropertyRelation[] An array of active property relations.
     */
    public function getAllActiveRelations(): array
    {
        return $this->propertyRelationRepository->findBy(['status' => PropertyStatus::ACTIVE->value]);
    }

    /**
     * Creates a new property relation.
     *
     * @param Property $property The child property.
     * @param Property $parent The parent property.
     */
    public function createRelation(Property $property, Property $parent): void
    {
        $relation = new PropertyRelation();
        $relation->setProperty($property);
        $relation->setParent($parent);

        $this->entityManager->persist($relation);
        $this->entityManager->flush();
    }

    /**
     * Soft deletes all active relations for a given property.
     *
     * @param Property $property The property for which to soft delete relations.
     */
    public function softDeleteRelationsForProperty(Property $property): void
    {
        $relations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        foreach ($relations as $relation) {
            $relation->setStatus(PropertyStatus::DELETED);
        }

        $this->entityManager->flush();
    }

    /**
     * Finds property relations based on given criteria.
     *
     * @param array $criteria The criteria to use for finding property relations.
     * @return PropertyRelation[] An array of property relations matching the criteria.
     */
    public function findBy(array $criteria): array
    {
        return $this->propertyRelationRepository->findBy($criteria);
    }

    /**
     * Retrieves all parents of a given property.
     *
     * @param Property $property The property for which to retrieve parents.
     * @return Property[] An array of parent properties.
     */
    public function getParents(Property $property): array
    {
        $parentRelations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        $parents = [];
        foreach ($parentRelations as $relation) {
            $parents[] = $relation->getParent();
        }
        return $parents;
    }

    /**
     * Retrieves all siblings of a given property.
     *
     * @param Property $property The property for which to retrieve siblings.
     * @return Property[] An array of sibling properties.
     */
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

    /**
     * Retrieves all children of a given property.
     *
     * @param Property $property The property for which to retrieve children.
     * @return Property[] An array of child properties.
     */
    public function getChildren(Property $property): array
    {
        $childRelations = $this->propertyRelationRepository->findBy(['parent' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        $children = [];
        foreach ($childRelations as $relation) {
            $children[] = $relation->getProperty();
        }
        return $children;
    }
    /**
     * Builds a property tree from given properties and relations.
     *
     * @param Property[] $properties An array of properties.
     * @param PropertyRelation[] $relations An array of property relations.
     * @return array A tree structure of properties.
     */
    public function buildPropertyTree(array $properties, array $relations): array
    {
        $propertyMap = $this->initializePropertyMap($properties);
        $this->buildParentChildRelationships($propertyMap, $relations);
        return $this->extractRootNodes($propertyMap, $relations);
    }

    /**
     * Initializes a map of properties.
     *
     * @param array $properties An array of properties.
     * @return array A map of properties.
     */
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

    /**
     * Builds parent-child relationships in the property map.
     *
     * @param array $propertyMap The map of properties.
     * @param array $relations The property relations.
     */
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

    /**
     * Extracts root nodes from the property map.
     *
     * @param array $propertyMap The map of properties.
     * @param array $relations The property relations.
     * @return array An array of root nodes.
     */
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

    /**
     * Checks if a property is a root node.
     *
     * @param int $propertyId The ID of the property.
     * @param array $relations The property relations.
     * @return bool True if the property is a root node, false otherwise.
     */
    private function isRootNode(int $propertyId, array $relations): bool
    {
        foreach ($relations as $relation) {
            if ($relation->getProperty()->getId() === $propertyId && $relation->getParent() !== null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates and creates a property relation.
     *
     * @param Property $property The child property.
     * @param Property $parent The parent property.
     * @param PropertyType $type The type of the property.
     * @throws \Exception If the relation is invalid.
     */
    public function validateAndCreateRelation(Property $property, Property $parent, PropertyType $type): void
    {
        $this->validateRelation($property, $parent, $type);
        $this->createRelation($property, $parent);
    }

    /**
     * Validates a property relation.
     *
     * @param Property $property The child property.
     * @param Property $parent The parent property.
     * @param PropertyType $type The type of the property.
     * @throws \Exception If the relation is invalid.
     */
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

    /**
     * Checks if a property has active children.
     *
     * @param Property $property The property to check.
     * @return bool True if the property has active children, false otherwise.
     */
    public function hasActiveChildren(Property $property): bool
    {
        return $this->propertyRelationRepository->findOneBy(['parent' => $property, 'status' => PropertyStatus::ACTIVE->value]) !== null;
    }

    /**
     * Soft deletes all active relations for a given property.
     *
     * @param Property $property The property for which to soft delete relations.
     */
    public function softDeleteRelations(Property $property): void
    {
        $relations = $this->propertyRelationRepository->findBy(['property' => $property, 'status' => PropertyStatus::ACTIVE->value]);
        foreach ($relations as $relation) {
            $relation->setStatus(PropertyStatus::DELETED);
        }

        $this->entityManager->flush();
    }
}
