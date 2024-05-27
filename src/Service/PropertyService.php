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

    public function __construct(PropertyRepository $propertyRepository, EntityManagerInterface $entityManager, PropertyRelationService $propertyRelationService)
    {
        $this->propertyRepository = $propertyRepository;
        $this->propertyRelationService = $propertyRelationService;
        $this->entityManager = $entityManager;
    }

    public function getAllActiveProperties(): array
    {
        return $this->propertyRepository->findBy(['status' => PropertyStatus::ACTIVE->value]);
    }

    public function saveProperty(Property $property): void
    {
        $this->entityManager->persist($property);
        $this->entityManager->flush();
    }

    public function findActivePropertyById($id): ?Property
    {
        return $this->propertyRepository->findOneBy(['id' => $id, 'status' => PropertyStatus::ACTIVE->value]);
    }

    public function softDeleteProperty(Property $property): void
    {
        $property->setStatus(PropertyStatus::DELETED);
        $this->entityManager->flush();
    }

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
