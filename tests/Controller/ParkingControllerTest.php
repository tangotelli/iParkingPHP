<?php

namespace App\Tests\Controller;

use App\Document\Status;
use App\Tests\BaseWebTestCase;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Faker\Factory as FakerFactoryAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParkingControllerTest extends BaseWebTestCase
{
    private static array $body;

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactoryAlias::create('es_ES');
        self::$body = [
            'name' => self::$faker->name(),
            'address' => self::$faker->address(),
            'booking_fare' => self::$faker->randomFloat(2, 0, 5),
            'stay_fare' => self::$faker->randomFloat(2, 0, 0.25),
            'latitude' => self::$faker->numberBetween(-20, 20),
            'longitude' => self::$faker->numberBetween(-20, 20),
        ];
    }

    public function testCreateSuccessful()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/parking/new',
            [],
            [],
            [],
            strval(json_encode(self::$body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $parking = json_decode(strval($response->getContent()), true);
        self::assertNotNull($parking['Id']);
        self::assertEquals(self::$body['name'], $parking['Name']);
        self::assertEquals(self::$body['address'], $parking['Address']);
        self::assertEquals(self::$body['booking_fare'], $parking['Booking Fare']);
        self::assertEquals(self::$body['stay_fare'], $parking['Stay Fare']);
        self::assertEquals(self::$body['latitude'], $parking['Location']['Latitude']);
        self::assertEquals(self::$body['longitude'], $parking['Location']['Longitude']);

        return $parking['Id'];
    }

    /**
     * @depends testCreateSuccessful
     */
    public function testGetSuccesful(string $parkingId)
    {
        self::$client->request(
            Request::METHOD_GET,
            '/parking/id/'.$parkingId
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $parking = json_decode(strval($response->getContent()), true);
        self::assertNotNull($parking['Id']);
        self::assertEquals(self::$body['name'], $parking['Name']);
        self::assertEquals(self::$body['address'], $parking['Address']);
        self::assertEquals(self::$body['booking_fare'], $parking['Booking Fare']);
        self::assertEquals(self::$body['stay_fare'], $parking['Stay Fare']);
        self::assertEquals(self::$body['latitude'], $parking['Location']['Latitude']);
        self::assertEquals(self::$body['longitude'], $parking['Location']['Longitude']);
    }

    public function testGetUnsuccessful()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/parking/id/1'
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testCreateSuccessful
     */
    public function testCreateSpotSuccessful(string $parkingId)
    {
        $spotBody = [
            'spotCode' => self::$faker->randomLetter(),
            'parkingId' => $parkingId,
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/parking/newSpot',
            [],
            [],
            [],
            strval(json_encode($spotBody))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $spot = json_decode(strval($response->getContent()), true);
        self::assertNotNull($spot['Id']);
        self::assertEquals($spotBody['spotCode'], $spot['Code']);
        self::assertEquals($parkingId, $spot['Parking Id']);
        self::assertEquals(self::$body['name'], $spot['Parking']);
        self::assertEquals(Status::FREE(), $spot['Status']);

        return $spot;
    }

    /**
     * @depends testCreateSpotSuccessful
     */
    public function testCreateSpotUnsuccesful(mixed $spot)
    {
        $spotBody = [
            'spotCode' => $spot['Code'],
            'parkingId' => $spot['Parking Id'],
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/parking/newSpot',
            [],
            [],
            [],
            strval(json_encode($spotBody))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @depends testCreateSuccessful
     */
    public function testFindAll()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/parking/all'
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @depends testCreateSuccessful
     */
    public function testFindClosestParkings()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/parking/closest?latitude='.self::$body['latitude'].'&longitude='.self::$body['longitude']
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @depends testCreateSpotSuccessful
     */
    public function testGetLevelOfOccupationSuccessful(mixed $spot)
    {
        self::$client->request(
            Request::METHOD_GET,
            '/parking/occupation/'.$spot['Parking Id']
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testGetLevelOfOccupationUnsuccesful()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/parking/occupation/1'
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public static function tearDownAfterClass(): void
    {
        $documentManager = self::getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
        $purger = new MongoDBPurger($documentManager);
        $purger->purge();
    }
}
