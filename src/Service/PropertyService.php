<?php

namespace App\Service;

use App\DTO\CreateOrUpdateRequest;
use App\Entity\Property;
use App\Enum\PropertyStatus;
use App\Enum\PropertyType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;

class PropertyService
{
    private $propertyRepository;
    private $entityManager;
    private $propertyRelationService;

    /**
     * @param PropertyRepository $propertyRepository The repository for property entities.
     * @param EntityManagerInterface $entityManager The entity manager.
     * @param PropertyRelationService $propertyRelationService The service for handling property relations.
     */
    public function __construct(PropertyRepository $propertyRepository, EntityManagerInterface $entityManager, PropertyRelationService $propertyRelationService)
    {
        $this->propertyRepository = $propertyRepository;
        $this->propertyRelationService = $propertyRelationService;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves all active properties.
     *
     * @return Property[] An array of active properties.
     */
    public function getAllActiveProperties(): array
    {
        return $this->propertyRepository->findBy(['status' => PropertyStatus::ACTIVE->value]);
    }

    /**
     * Saves a property to the database.
     *
     * @param Property $property The property to save.
     */
    public function saveProperty(Property $property): void
    {
        $this->entityManager->persist($property);
        $this->entityManager->flush();
    }

    /**
     * Finds an active property by its ID.
     *
     * @param int $id The ID of the property.
     * @return Property|null The found property or null if not found.
     */
    public function findActivePropertyById($id): ?Property
    {
        return $this->propertyRepository->findOneBy(['id' => $id, 'status' => PropertyStatus::ACTIVE->value]);
    }

    /**
     * Soft deletes a property by setting its status to DELETED.
     *
     * @param Property $property The property to soft delete.
     */
    public function softDeleteProperty(Property $property): void
    {
        $property->setStatus(PropertyStatus::DELETED);
        $this->entityManager->flush();
    }

    /**
     * Assembles property details including its parents, siblings, and children.
     *
     * @param Property $property The main property.
     * @param Property[] $parents The parent properties.
     * @param Property[] $siblings The sibling properties.
     * @param Property[] $children The child properties.
     * 
     * @return array An array of property details with their relations.
     */
    public function assemblePropertyDetails(Property $property, array $parents, array $siblings, array $children): array
    {
        $result = [];

        // Add the selected property itself
        $result[] = ['property' => $property->getName(), 'relation' => null];

        // Add parents
        foreach ($parents as $parent) {
            $result[] = ['property' => $parent->getName(), 'relation' => 'parent'];
        }

        // Add siblings
        foreach ($siblings as $sibling) {
            if ($sibling !== $property) {
                $result[] = ['property' => $sibling->getName(), 'relation' => 'sibling'];
            }
        }

        // Add children
        foreach ($children as $child) {
            $result[] = ['property' => $child->getName(), 'relation' => 'child'];
        }

        // Sort the result by property name
        usort($result, function ($a, $b) {
            return strcmp($a['property'], $b['property']);
        });

        return $result;
    }

    /**
     * Creates or updates a property.
     *
     * @param CreateOrUpdateRequest $data
     * @return Property
     * @throws \Exception
     */
    public function createOrUpdateProperty(CreateOrUpdateRequest $data): Property
    {
        $type = PropertyType::fromString($data->getType());
        $property = $this->propertyRepository->findOneBy(['name' => $data->getName(), 'status' => PropertyStatus::ACTIVE->value]);

        if (!$property) {
            $property = new Property();
            $property->setName($data->getName());
            $property->setType($type);

            $this->saveProperty($property);
        } else {
            if ($type !== $property->getType()) {
                throw new \Exception('Existing property type mismatch');
            }
        }

        if ($data->getParentId() !== null) {
            $parent = $this->findActivePropertyById($data->getParentId());
            if (!$parent) {
                throw new \Exception('Parent property not found');
            }

            $this->propertyRelationService->validateAndCreateRelation($property, $parent, $type);
        }

        return $property;
    }
}
