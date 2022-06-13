<?php

namespace App\Tests\Service;

use App\Document\Booking;
use App\Document\Location;
use App\Document\Parking;
use App\Document\Spot;
use App\Document\Status;
use App\Document\Stay;
use App\Document\User;
use App\Document\Vehicle;
use App\Service\StayService;
use App\Service\UserService;
use App\Tests\BaseIntegrationTestCase;
use DateInterval;

class StayServiceTest extends BaseIntegrationTestCase
{
    private StayService $stayService;
    private UserService $userService;
    private User $userA;
    private User $userB;
    private Vehicle $vehicleA;
    private Vehicle $vehicleB;
    private Vehicle $vehicleC;
    private Parking $parking;
    private Spot $spotA;
    private Spot $spotB;
    private Spot $spotC;
    private Stay $stay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stayService = $this->getContainer()
            ->get(StayService::class);
        $this->userService = $this->getContainer()
            ->get(UserService::class);
        $this->persistParking();
        $this->persistSpots();
        $this->persistUser();
        $this->persistVehicles();
        $this->persistBooking();
        $this->persistStay();
    }

    private function persistParking(): void
    {
        $this->parking = new Parking();
        $this->parking->setName('Parking Salesas');
        $this->parking->setAddress('General Elorza 75');
        $this->parking->setBookingFare(6.17);
        $this->parking->setStayFare(0.08);
        $this->parking->setLocation(new Location(43.367, -5.849));
        $this->documentManager->persist($this->parking);
        $this->documentManager->flush();
    }

    private function persistSpots()
    {
        $this->spotA = new Spot();
        $this->spotA->setCode('A1');
        $this->spotA->setParking($this->parking);
        $this->spotA->setStatus(Status::FREE());
        $this->documentManager->persist($this->spotA);
        $this->spotB = new Spot();
        $this->spotB->setCode('B1');
        $this->spotB->setParking($this->parking);
        $this->spotB->setStatus(Status::BOOKED());
        $this->documentManager->persist($this->spotB);
        $this->spotC = new Spot();
        $this->spotC->setCode('C1');
        $this->spotC->setParking($this->parking);
        $this->spotC->setStatus(Status::OCCUPIED());
        $this->documentManager->persist($this->spotC);
    }

    private function persistUser(): void
    {
        $this->userA = new User();
        $this->userA->setName('Dummy');
        $this->userA->setEmail('dummy@hotmail.com');
        $this->userA->setPassword('contraseña1');
        $this->userService->signin($this->userA);
        $this->userB = new User();
        $this->userB->setName('John Doe');
        $this->userB->setEmail('johndoe@yahoo.us');
        $this->userB->setPassword('w0rdp4s$');
        $this->userService->signin($this->userB);
    }

    private function persistVehicles()
    {
        $this->vehicleA = new Vehicle();
        $this->vehicleA->setNickname('Seat Ibiza');
        $this->vehicleA->setLicensePlate('4657 FGT');
        $this->vehicleA->setUser($this->userA);
        $this->documentManager->persist($this->vehicleA);
        $this->vehicleB = new Vehicle();
        $this->vehicleB->setNickname('Mondeo');
        $this->vehicleB->setLicensePlate('4328 DBT');
        $this->vehicleB->setUser($this->userB);
        $this->documentManager->persist($this->vehicleB);
        $this->vehicleC = new Vehicle();
        $this->vehicleC->setNickname('Furgo');
        $this->vehicleC->setLicensePlate('1526 BBS');
        $this->vehicleC->setUser($this->userB);
        $this->documentManager->persist($this->vehicleC);
        $this->documentManager->flush();
    }

    private function persistBooking()
    {
        $booking = new Booking();
        $start = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $end = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $interval = new DateInterval('PT1H');
        $end->add($interval);
        $booking->setStart($start);
        $booking->setEnd($end);
        $booking->setSpot($this->spotB);
        $booking->setVehicle($this->vehicleB);
        $booking->calculatePrice();
        $this->documentManager->persist($booking);
        $this->documentManager->flush();
    }

    private function persistStay()
    {
        $this->stay = new Stay();
        $this->stay->setStart(new \DateTime('now', new \DateTimeZone('Europe/Madrid')));
        $this->stay->setSpot($this->spotC);
        $this->stay->setVehicle($this->vehicleC);
        $this->documentManager->persist($this->stay);
        $this->documentManager->flush();
    }

    public function testBeginStay()
    {
        $stay = $this->stayService->beginStay($this->parking->getId(), $this->vehicleA);
        self::assertNotNull($stay);
        self::assertNotNull($stay->getId());
        self::assertEquals($this->vehicleA, $stay->getVehicle());
        self::assertEquals($this->spotA, $stay->getSpot());
        self::assertEquals(Status::OCCUPIED(), $stay->getSpot()->getStatus());
    }

    public function testBeginStayFromBooking()
    {
        $stay = $this->stayService->beginStayFromBooking($this->spotB, $this->vehicleB);
        self::assertNotNull($stay);
        self::assertNotNull($stay->getId());
        self::assertEquals($this->vehicleB, $stay->getVehicle());
        self::assertEquals($this->spotB, $stay->getSpot());
        self::assertEquals(Status::OCCUPIED(), $stay->getSpot()->getStatus());
    }

    public function testEndStay()
    {
        /** @var Stay $stay */
        $stay = $this->documentManager->getRepository(Stay::class)->find($this->stay->getId());
        self::assertNotNull($stay);
        self::assertEquals($this->spotC, $stay->getSpot());
        self::assertEquals(Status::OCCUPIED(), $stay->getSpot()->getStatus());
        $stayEnded = $this->stayService->endStay($stay);
        self::assertNotNull($stayEnded);
        self::assertNotNull($stayEnded->getEnd());
        self::assertEquals($stay->getId(), $stayEnded->getId());
        self::assertEquals($this->spotC, $stayEnded->getSpot());
        self::assertEquals(Status::FREE(), $stayEnded->getSpot()->getStatus());
    }

    public function testExistsActiveStay()
    {
        self::assertTrue($this->stayService->existsActiveStay($this->parking->getId(), $this->vehicleC));
        self::assertFalse($this->stayService->existsActiveStay($this->parking->getId(), $this->vehicleB));
        self::assertFalse($this->stayService->existsActiveStay($this->parking->getId(), $this->vehicleA));
    }

    public function testGet()
    {
        self::assertEquals($this->stay, $this->stayService->get($this->stay->getId()));
        self::assertNull($this->stayService->get('randomId'));
    }

    public function testResumeStay()
    {
        /** @var Stay $stay */
        $stay = $this->documentManager->getRepository(Stay::class)->find($this->stay->getId());
        self::assertNotNull($stay);
        self::assertEquals($this->spotC, $stay->getSpot());
        self::assertEquals(Status::OCCUPIED(), $stay->getSpot()->getStatus());
        $stayEnded = $this->stayService->endStay($stay);
        self::assertNotNull($stayEnded);
        self::assertNotNull($stayEnded->getEnd());
        self::assertEquals($stay->getId(), $stayEnded->getId());
        self::assertEquals($this->spotC, $stayEnded->getSpot());
        self::assertEquals(Status::FREE(), $stayEnded->getSpot()->getStatus());
        $stayResumed = $this->stayService->resumeStay($stayEnded);
        self::assertNotNull($stayResumed);
        self::assertEquals($stayEnded->getId(), $stayResumed->getId());
        self::assertEquals($this->spotC, $stayResumed->getSpot());
        self::assertEquals(Status::OCCUPIED(), $stayResumed->getSpot()->getStatus());
    }

    public function testFindActiveStayByUserVehicles()
    {
        $vehicles = [$this->vehicleB, $this->vehicleC];
        $stay = $this->stayService->findActiveStayByUserVehicles($vehicles);
        self::assertNotNull($stay);
        self::assertEquals($this->spotC, $stay->getSpot());
        self::assertEquals($this->vehicleC, $stay->getVehicle());
        self::assertEquals(Status::OCCUPIED(), $stay->getSpot()->getStatus());
        $otherVehicles = [$this->vehicleA, $this->vehicleB];
        self::assertNull($this->stayService->findActiveStayByUserVehicles($otherVehicles));
    }
}
