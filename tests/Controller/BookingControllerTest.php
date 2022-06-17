<?php

namespace App\Tests\Controller;

use App\Document\Location;
use App\Document\Parking;
use App\Document\Spot;
use App\Document\User;
use App\Document\Vehicle;
use App\Service\ParkingService;
use App\Service\SpotService;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Tests\BaseWebTestCase;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Faker\Factory as FakerFactoryAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookingControllerTest extends BaseWebTestCase
{
    private User $user;
    private Parking $parking;
    private Spot $spot;
    private Vehicle $vehicle;

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactoryAlias::create('es_ES');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->persistUser();
        $this->persistVehicle();
        $this->persistParking();
        $this->persistSpot();
    }

    private function persistUser()
    {
        $this->user = new User();
        $this->user->setName(self::$faker->name());
        $this->user->setEmail(self::$faker->email());
        $this->user->setPassword(self::$faker->word());
        $userService = $this->getContainer()->get(UserService::class);
        $userService->signin($this->user);
    }

    private function persistVehicle()
    {
        $this->vehicle = new Vehicle();
        $this->vehicle->setNickname(self::$faker->word());
        $this->vehicle->setLicensePlate(self::$faker->word());
        $this->vehicle->setUser($this->user);
        $vehicleService = $this->getContainer()->get(VehicleService::class);
        $vehicleService->register($this->vehicle);
    }

    private function persistParking()
    {
        $this->parking = new Parking();
        $this->parking->setName(self::$faker->name());
        $this->parking->setAddress(self::$faker->address());
        $this->parking->setBookingFare(self::$faker->randomFloat(2, 0, 5));
        $this->parking->setStayFare(self::$faker->randomFloat(2, 0, 0.25));
        $location = new Location(self::$faker->numberBetween(-20, 20),
            self::$faker->numberBetween(-20, 20));
        $this->parking->setLocation($location);
        $parkingService = $this->getContainer()->get(ParkingService::class);
        $parkingService->create($this->parking);
    }

    private function persistSpot()
    {
        $spotService = $this->getContainer()->get(SpotService::class);
        $this->spot = $spotService->create($this->parking, self::$faker->randomLetter());
    }

    public function testBookSpotSuccessful()
    {
        $body = [
            'parkingId' => $this->parking->getId(),
            'email' => $this->user->getEmail(),
            'vehicle' => $this->vehicle->getNickname(),
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/booking/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $booking = json_decode(strval($response->getContent()), true);
        self::assertNotNull($booking['Id']);
        self::assertEquals($this->parking->getId(), $booking['Parking Id']);
        self::assertEquals($this->parking->getName(), $booking['Parking']);
        self::assertEquals($this->spot->getCode(), $booking['Spot']);
        self::assertEquals($this->vehicle->getNickname(), $booking['Vehicle']);
        self::assertEquals($this->user->getEmail(), $booking['User']);
        self::assertEquals($this->parking->getBookingFare(), $booking['Price']);

        return $booking;
    }

    public function testBookSpotUnsuccessfulWrongUser()
    {
        $body = [
            'parkingId' => $this->parking->getId(),
            'email' => '1',
            'vehicle' => $this->vehicle->getNickname(),
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/booking/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testBookSpotUnsuccessfulWrongVehicle()
    {
        $body = [
            'parkingId' => $this->parking->getId(),
            'email' => $this->user->getEmail(),
            'vehicle' => '1',
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/booking/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testBookSpotSuccessful
     */
    public function testBookSpotUnsuccessfulForbidden(mixed $booking)
    {
        $body = [
            'parkingId' => $booking['Parking Id'],
            'email' => $booking['User'],
            'vehicle' => $booking['Vehicle'],
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/booking/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @depends testBookSpotSuccessful
     */
    public function testBookSpotUnsuccesfulNoSpot(mixed $booking)
    {
        $body = [
            'parkingId' => $booking['Parking Id'],
            'email' => $this->user->getEmail(),
            'vehicle' => $this->vehicle->getNickname(),
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/booking/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
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
