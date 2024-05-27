<?php

namespace App\Service;

use App\DTO\CreateOrUpdateRequest;
use App\Entity\Property;
use App\Enum\PropertyStatus;
use App\Enum\PropertyType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PropertyService
{
    private $propertyRepository;
    private $entityManager;
    private $validator;

    public function __construct(PropertyRepository $propertyRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->propertyRepository = $propertyRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function getAllActiveProperties(): array
    {
        return $this->propertyRepository->findBy(['status' => PropertyStatus::ACTIVE->value]);
    }

    public function validateAndSaveProperty(Property $property): array
    {
        $errors = $this->validator->validate($property);
        if (count($errors) > 0) {
            return $errors;
        }

        $this->entityManager->persist($property);
        $this->entityManager->flush();

        return [];
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

    public function createOrUpdateProperty(CreateOrUpdateRequest $data): array
    {
        $type = PropertyType::fromString($data->getType());
        $propertyResult = $this->findOrCreateProperty($data->getName(), $type);
        if (isset($propertyResult['error'])) {
            return $propertyResult;
        }

        $property = $propertyResult['property'];
        $errors = $this->validateAndSaveProperty($property);
        if (!empty($errors)) {
            return $errors;
        }

        return ['property' => $property, 'type' => $type, 'errors' => $errors];
    }

    private function findOrCreateProperty(string $name, PropertyType $type): array
    {
        $property = $this->propertyRepository->findOneBy(['name' => $name, 'status' => PropertyStatus::ACTIVE->value]);
        if (!$property) {
            $property = new Property();
            $property->setName($name);
            $property->setType($type);
        } else {
            if ($type !== $property->getType()) {
                return ['error' => 'Existing property type mismatch'];
            }
        }

        return ['property' => $property];
    }
}
