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

class StayControllerTest extends BaseWebTestCase
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

    public function testBeginStaySuccessful()
    {
        $body = [
            'parkingId' => $this->parking->getId(),
            'email' => $this->user->getEmail(),
            'vehicle' => $this->vehicle->getNickname(),
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/stay/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $stay = json_decode(strval($response->getContent()), true);
        self::assertNotNull($stay['Id']);
        self::assertEquals($this->parking->getId(), $stay['Parking Id']);
        self::assertEquals($this->parking->getName(), $stay['Parking']);
        self::assertEquals($this->spot->getCode(), $stay['Spot']);
        self::assertEquals($this->vehicle->getNickname(), $stay['Vehicle']);
        self::assertEquals($this->user->getEmail(), $stay['User']);
        self::assertNotEquals('null', $stay['Beginning']);
        self::assertEquals('null', $stay['End']);
        self::assertEquals('null', $stay['Price']);

        return $stay;
    }

    public function testBeginStayUnsuccessfulWrongUser()
    {
        $body = [
            'parkingId' => $this->parking->getId(),
            'email' => '1',
            'vehicle' => $this->vehicle->getNickname(),
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/stay/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testBeginStayUnsuccessfulWrongVehicle()
    {
        $body = [
            'parkingId' => $this->parking->getId(),
            'email' => $this->user->getEmail(),
            'vehicle' => '1',
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/stay/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testBeginStaySuccessful
     */
    public function testBeginStayUnsuccessfulForbidden(mixed $stay)
    {
        $body = [
            'parkingId' => $stay['Parking Id'],
            'email' => $stay['User'],
            'vehicle' => $stay['Vehicle'],
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/stay/new',
            [],
            [],
            [],
            strval(json_encode($body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @depends testBeginStaySuccessful
     */
    public function testFindStayByUser(mixed $stay)
    {
        self::$client->request(
            Request::METHOD_GET,
            '/stay/get?email='.$stay['User']
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseStay = json_decode(strval($response->getContent()), true);
        self::assertNotNull($responseStay['Id']);
        self::assertEquals($stay['Id'], $responseStay['Id']);
        self::assertEquals($stay['Parking Id'], $responseStay['Parking Id']);
        self::assertEquals($stay['Parking'], $responseStay['Parking']);
        self::assertEquals($stay['Spot'], $responseStay['Spot']);
        self::assertEquals($stay['Vehicle'], $responseStay['Vehicle']);
        self::assertEquals($stay['User'], $responseStay['User']);

        return $stay;
    }

    public function testFindStayByUserUnsuccesfulWrongUser()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/stay/get?email=1'
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testFindStayByUserUnsuccesfulNoStay()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/stay/get?email='.$this->user->getEmail()
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testFindStayByUser
     */
    public function testEndStaySuccessful(mixed $stay)
    {
        self::$client->request(
            Request::METHOD_PUT,
            '/stay/end/'.$stay['Id']
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseStay = json_decode(strval($response->getContent()), true);
        self::assertNotNull($responseStay['Id']);
        self::assertEquals($stay['Id'], $responseStay['Id']);
        self::assertEquals($stay['Parking Id'], $responseStay['Parking Id']);
        self::assertEquals($stay['Parking'], $responseStay['Parking']);
        self::assertEquals($stay['Spot'], $responseStay['Spot']);
        self::assertEquals($stay['Vehicle'], $responseStay['Vehicle']);
        self::assertEquals($stay['User'], $responseStay['User']);
        self::assertNotEquals('null', $responseStay['Beginning']);
        self::assertNotEquals('null', $responseStay['End']);

        return $responseStay;
    }

    public function testEndStayUnsuccessful()
    {
        self::$client->request(
            Request::METHOD_PUT,
            '/stay/end/1'
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testEndStaySuccessful
     */
    public function testResumeStaySuccessful(mixed $stay)
    {
        self::$client->request(
            Request::METHOD_PUT,
            '/stay/resume/'.$stay['Id']
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseStay = json_decode(strval($response->getContent()), true);
        self::assertNotNull($responseStay['Id']);
        self::assertEquals($stay['Id'], $responseStay['Id']);
        self::assertEquals($stay['Parking Id'], $responseStay['Parking Id']);
        self::assertEquals($stay['Parking'], $responseStay['Parking']);
        self::assertEquals($stay['Spot'], $responseStay['Spot']);
        self::assertEquals($stay['Vehicle'], $responseStay['Vehicle']);
        self::assertEquals($stay['User'], $responseStay['User']);
        self::assertNotEquals('null', $responseStay['Beginning']);
        self::assertEquals('null', $responseStay['End']);
    }

    public function testResumeStayUnsuccessful()
    {
        self::$client->request(
            Request::METHOD_PUT,
            '/stay/resume/1'
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
