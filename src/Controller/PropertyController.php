<?php

namespace App\Controller;

use App\Service\PropertyService;
use App\Service\PropertyRelationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\DTO\CreateOrUpdateRequest;

class PropertyController extends AbstractController
{
    private $propertyService;
    private $propertyRelationService;

    public function __construct(PropertyService $propertyService, PropertyRelationService $propertyRelationService)
    {
        $this->propertyService = $propertyService;
        $this->propertyRelationService = $propertyRelationService;
    }

    #[Route('/api/properties', methods: ['GET'])]
    public function getProperties(): JsonResponse
    {
        $properties = $this->propertyService->getAllActiveProperties();
        $relations = $this->propertyRelationService->getAllActiveRelations();
        $tree = $this->propertyRelationService->buildPropertyTree($properties, $relations);

        return $this->json($tree);
    }

    #[Route('/api/properties', methods: ['POST'], format: 'json')]
    public function addProperty(
        #[MapRequestPayload(acceptFormat: 'json', validationGroups: ['Default'], validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] 
        CreateOrUpdateRequest $request
    ): JsonResponse
    {
        $result = $this->propertyService->createOrUpdateProperty($request);
        if (isset($result['error'])) {
            return $this->json(['error' => $result['error']], Response::HTTP_BAD_REQUEST);
        }

        $property = $result['property'];
        $type = $result['type'];
        $errors = $result['errors'];

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        
        if ($request->getParentId() !== null) {
            $parent = $this->propertyService->findActivePropertyById($request->getParentId());
            if (!$parent) {
                return $this->json(['error' => 'Parent property not found'], Response::HTTP_BAD_REQUEST);
            }

            $relationErrors = $this->propertyRelationService->validateAndCreateRelation($property, $parent, $type);
            if (isset($relationErrors['error'])) {
                return $this->json(['error' => $relationErrors['error']], Response::HTTP_BAD_REQUEST);
            }

            if (count($relationErrors) > 0) {
                return $this->json($relationErrors, Response::HTTP_BAD_REQUEST);
            }
        }

        $this->propertyService->validateAndSaveProperty($property);

        return $this->json($property, Response::HTTP_CREATED);
    }

    #[Route('/api/properties/{id}', methods: ['GET'])]
    public function getProperty(int $id): JsonResponse
    {
        $property = $this->propertyService->findActivePropertyById($id);
        if (!$property) {
            return $this->json(['error' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        $parents = $this->propertyRelationService->getParents($property);
        $siblings = $this->propertyRelationService->getSiblings($property);
        $children = $this->propertyRelationService->getChildren($property);

        $result = $this->propertyService->assemblePropertyDetails($property, $parents, $siblings, $children);

        return $this->json($result);
    }

    #[Route('/api/properties/{id}', methods: ['DELETE'])]
    public function deleteProperty(int $id): JsonResponse
    {
        $property = $this->propertyService->findActivePropertyById($id);
        if (!$property) {
            return $this->json(['error' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        if ($this->propertyRelationService->hasActiveChildren($property)) {
            return $this->json(['error' => 'Cannot delete property with active children'], Response::HTTP_BAD_REQUEST);
        }

        $this->propertyRelationService->softDeleteRelations($property);
        $this->propertyService->softDeleteProperty($property);

        return new JsonResponse(['message' => 'Property and its relations soft deleted'], Response::HTTP_OK);
    }
}
