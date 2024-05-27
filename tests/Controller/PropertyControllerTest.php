<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PropertyControllerTest extends WebTestCase
{
    private static $client = null;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        parent::tearDown();
    }

    private function cleanDatabase(): void
    {
        $entityManager = self::$client->getContainer()->get('doctrine')->getManager();
        $entityManager->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $entityManager->createQuery('DELETE FROM App\Entity\Property')->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\PropertyRelation')->execute();
        $entityManager->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        $entityManager->close();
    }

    private function createProperty(array $data)
    {
        self::$client->request('POST', '/api/properties', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        return json_decode(self::$client->getResponse()->getContent(), true);
    }

    public function testAddProperty()
    {
        $building = $this->createProperty([
            'name' => 'Building',
            'type' => 'property'
        ]);

        $this->assertEquals(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('id', $building);
    }

    public function testAddPropertyWithEmptyName()
    {
        $response = $this->createProperty([
            'name' => '',
            'type' => 'property'
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, self::$client->getResponse()->getStatusCode());

        $responseContent = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertEquals('Name must be at least 1 characters long', $responseContent['error']);
    }

    public function testAddPropertyWithInvalidType()
    {
        $response = $this->createProperty([
            'name' => 'Building',
            'type' => 'invalid_type'
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, self::$client->getResponse()->getStatusCode());

        $responseContent = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertEquals('Invalid property type: "invalid_type"', $responseContent['error']);
    }

    public function testDeleteProperty()
    {
        $building = $this->createProperty([
            'name' => 'Building',
            'type' => 'property'
        ]);

        self::$client->request('DELETE', '/api/properties/' . $building['id']);
        $this->assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
    }

    public function testDeletePropertyWithActiveChildren()
    {
        $buildingComplex = $this->createProperty([
            'name' => 'Building complex',
            'type' => 'property'
        ]);

        $this->createProperty([
            'name' => 'Child Property',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        self::$client->request('DELETE', '/api/properties/' . $buildingComplex['id']);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, self::$client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Cannot delete property with active children', self::$client->getResponse()->getContent());
    }

    public function testNestedPropertyTreeWithSharedParkingSpace()
    {
        $buildingComplex = $this->createProperty([
            'name' => 'Building complex',
            'type' => 'property'
        ]);

        $building1 = $this->createProperty([
            'name' => 'Building 1',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        $building2 = $this->createProperty([
            'name' => 'Building 2',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        $building3 = $this->createProperty([
            'name' => 'Building 3',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        $parkingSpace1 = $this->createProperty([
            'name' => 'Parking space 1',
            'type' => 'parking_space',
            'parent_id' => $building2['id']
        ]);

        $this->createProperty([
            'name' => $parkingSpace1['name'],
            'type' => 'parking_space',
            'parent_id' => $building1['id']
        ]);

        $parkingSpace2 = $this->createProperty([
            'name' => 'Parking space 2',
            'type' => 'parking_space',
            'parent_id' => $building2['id']
        ]);

        self::$client->request('GET', '/api/properties');

        $responseContent = self::$client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $expectedResponse = [
            [
                "id"       => $buildingComplex['id'],
                "name"     => "Building complex",
                "type"     => "property",
                "created"  => $buildingComplex['created'],
                "modified" => $buildingComplex['modified'],
                "status"   => "active",
                "children" => [
                    [
                        "id"       => $building1['id'],
                        "name"     => "Building 1",
                        "type"     => "property",
                        "created"  => $building1['created'],
                        "modified" => $building1['modified'],
                        "status"   => "active",
                        "children" => [
                            [
                                "id"       => $parkingSpace1['id'],
                                "name"     => "Parking space 1",
                                "type"     => "parking_space",
                                "created"  => $parkingSpace1['created'],
                                "modified" => $parkingSpace1['modified'],
                                "status"   => "active",
                                "children" => []
                            ],
                        ]
                    ],
                    [
                        "id"       => $building2['id'],
                        "name"     => "Building 2",
                        "type"     => "property",
                        "created"  => $building2['created'],
                        "modified" => $building2['modified'],
                        "status"   => "active",
                        "children" => [
                            [
                                "id"       => $parkingSpace1['id'],
                                "name"     => "Parking space 1",
                                "type"     => "parking_space",
                                "created"  => $parkingSpace1['created'],
                                "modified" => $parkingSpace1['modified'],
                                "status"   => "active",
                                "children" => []
                            ],
                            [
                                "id"       => $parkingSpace2['id'],
                                "name"     => "Parking space 2",
                                "type"     => "parking_space",
                                "created"  => $parkingSpace2['created'],
                                "modified" => $parkingSpace2['modified'],
                                "status"   => "active",
                                "children" => []
                            ]
                        ]
                    ],
                    [
                        "id"       => $building3['id'],
                        "name"     => "Building 3",
                        "type"     => "property",
                        "created"  => $building3['created'],
                        "modified" => $building3['modified'],
                        "status"   => "active",
                        "children" => []
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResponse, $responseData);
    }

    public function testGetPropertyWithFlattenedStructure()
    {
        $buildingComplex = $this->createProperty([
            'name' => 'Building complex',
            'type' => 'property'
        ]);

        $this->createProperty([
            'name' => 'Building 1',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        $building2 = $this->createProperty([
            'name' => 'Building 2',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        $this->createProperty([
            'name' => 'Building 3',
            'type' => 'property',
            'parent_id' => $buildingComplex['id']
        ]);

        $this->createProperty([
            'name' => 'Parking space 1',
            'type' => 'parking_space',
            'parent_id' => $building2['id']
        ]);

        $this->createProperty([
            'name' => 'Parking space 2',
            'type' => 'parking_space',
            'parent_id' => $building2['id']
        ]);

        self::$client->request('GET', '/api/properties/' . $building2['id']);

        $responseContent = self::$client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $expectedResponse = [
            ['property' => 'Building 1', 'relation' => 'sibling'],
            ['property' => 'Building 2', 'relation' => null],
            ['property' => 'Building 3', 'relation' => 'sibling'],
            ['property' => 'Building complex', 'relation' => 'parent'],
            ['property' => 'Parking space 1', 'relation' => 'child'],
            ['property' => 'Parking space 2', 'relation' => 'child']
        ];

        $this->assertEquals($expectedResponse, $responseData);
    }
}
