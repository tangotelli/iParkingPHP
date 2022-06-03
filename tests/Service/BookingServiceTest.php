<?php

namespace App\Tests\Service;

use App\Document\Booking;
use App\Document\Location;
use App\Document\Parking;
use App\Document\Spot;
use App\Document\Status;
use App\Document\User;
use App\Document\Vehicle;
use App\Service\BookingService;
use App\Service\UserService;
use DateInterval;

class BookingServiceTest extends \App\Tests\BaseIntegrationTestCase
{
    private BookingService $bookingService;
    private UserService $userService;
    private User $userA;
    private User $userB;
    private Vehicle $vehicleA;
    private Vehicle $vehicleB;
    private Parking $parking;
    private Spot $spotA;
    private Spot $spotB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = $this->kernelInterface->getContainer()
            ->get(BookingService::class);
        $this->userService = $this->kernelInterface->getContainer()
            ->get(UserService::class);
        $this->persistParking();
        $this->persistSpots();
        $this->persistUser();
        $this->persistVehicles();
        $this->persistBooking();
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

    public function testBookSpot()
    {
        $booking = $this->bookingService->bookSpot($this->parking->getId(), $this->vehicleA);
        self::assertNotNull($booking);
        self::assertNotNull($booking->getId());
        self::assertEquals($this->vehicleA, $booking->getVehicle());
        self::assertEquals($this->spotA, $booking->getSpot());
        self::assertEquals($this->parking->getBookingFare(), $booking->getPrice());
        self::assertEquals(1, $booking->getStart()->diff($booking->getEnd())->h);
        self::assertEquals(0, $booking->getStart()->diff($booking->getEnd())->d);
        self::assertEquals(0, $booking->getStart()->diff($booking->getEnd())->m);
    }

    public function testAnySpotFree()
    {
        self::assertTrue($this->bookingService->anySpotFree($this->parking->getId()));
        $this->bookingService->bookSpot($this->parking->getId(), $this->vehicleA);
        self::assertFalse($this->bookingService->anySpotFree($this->parking->getId()));
    }

    public function testFindActiveBooking()
    {
        self::assertNull($this->bookingService->findActiveBooking($this->userA, $this->parking->getId()));
        self::assertNotNull($this->bookingService->findActiveBooking($this->userB, $this->parking->getId()));
        $booking = $this->bookingService->findActiveBooking($this->userB, $this->parking->getId());
        self::assertNotNull($booking);
        self::assertNotNull($booking->getId());
        self::assertEquals($this->vehicleB, $booking->getVehicle());
        self::assertEquals($this->spotB, $booking->getSpot());
        self::assertEquals($this->parking->getBookingFare(), $booking->getPrice());
        self::assertEquals(1, $booking->getStart()->diff($booking->getEnd())->h);
        self::assertEquals(0, $booking->getStart()->diff($booking->getEnd())->d);
        self::assertEquals(0, $booking->getStart()->diff($booking->getEnd())->m);
    }

    public function testExistsActiveBooking()
    {
        self::assertTrue($this->bookingService->existsActiveBooking($this->parking->getId(), $this->vehicleB));
        self::assertFalse($this->bookingService->existsActiveBooking($this->parking->getId(), $this->vehicleA));
    }
}
