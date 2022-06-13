<?php

namespace App\Tests\Service;

use App\Document\Location;
use App\Document\Parking;
use App\Service\ParkingService;
use App\Tests\BaseIntegrationTestCase;

class ParkingServiceTest extends BaseIntegrationTestCase
{
    private ParkingService $parkingService;
    private string $parkingId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parkingService = $this->getContainer()
            ->get(ParkingService::class);
        $this->persistParking();
    }

    private function persistParking(): void
    {
        $parking = new Parking();
        $parking->setName('Parking Salesas');
        $parking->setAddress('General Elorza 75');
        $parking->setBookingFare(6.17);
        $parking->setStayFare(0.08);
        $parking->setLocation(new Location(43.367, -5.849));
        $this->documentManager->persist($parking);
        $this->documentManager->flush();
        $this->parkingId = $parking->getId();
    }

    public function testCreate()
    {
        $parking = new Parking();
        $parking->setName('Gora Aparcamientos');
        $parking->setAddress('Vía Salada 98');
        $parking->setBookingFare(3.5);
        $parking->setStayFare(0.11);
        $parking->setLocation(new Location(-21.367, 7.849));
        $this->parkingService->create($parking);
        self::assertNotNull($parking->getId());
    }

    public function testGet()
    {
        /** @var Parking $parking */
        $parking = $this->parkingService->get($this->parkingId);
        self::assertNotNull($parking);
        self::assertEquals('Parking Salesas', $parking->getName());
        self::assertEquals('General Elorza 75', $parking->getAddress());
        self::assertEquals(6.17, $parking->getBookingFare());
        self::assertEquals(0.08, $parking->getStayFare());
        self::assertEquals(new Location(43.367, -5.849), $parking->getLocation());
    }

    public function testFindAll()
    {
        $parkings = $this->parkingService->findAll();
        self::assertEquals(1, count($parkings));
        self::assertEquals($this->parkingId, $parkings[0]->getId());
        self::assertEquals('Parking Salesas', $parkings[0]->getName());
        $parking = new Parking();
        $parking->setName('Gora Aparcamientos');
        $parking->setAddress('Vía Salada 98');
        $parking->setBookingFare(3.5);
        $parking->setStayFare(0.11);
        $parking->setLocation(new Location(-21.367, 7.849));
        $this->parkingService->create($parking);
        $parkings = $this->parkingService->findAll();
        self::assertEquals(2, count($parkings));
        self::assertEquals($this->parkingId, $parkings[0]->getId());
        self::assertEquals('Parking Salesas', $parkings[0]->getName());
        self::assertEquals('Gora Aparcamientos', $parkings[1]->getName());
        self::assertEquals(new Location(-21.367, 7.849), $parkings[1]->getLocation());
    }

    public function testFindClosestParkings()
    {
        $parkings = $this->parkingService->findClosestParkings(new Location(43.111, -5.919));
        self::assertEquals(1, count($parkings->toArray()));
        self::assertEquals($this->parkingId, $parkings->toArray()[0]->getId());
        self::assertEquals('Parking Salesas', $parkings->toArray()[0]->getName());
        self::assertEquals(new Location(43.367, -5.849), $parkings->toArray()[0]->getLocation());
        $parking = new Parking();
        $parking->setName('Gora Aparcamientos');
        $parking->setAddress('Vía Salada 98');
        $parking->setBookingFare(3.5);
        $parking->setStayFare(0.11);
        $parking->setLocation(new Location(43.111, -5.919));
        $this->parkingService->create($parking);
        $parkings = $this->parkingService->findClosestParkings(new Location(43.111, -5.919));
        self::assertEquals(2, count($parkings->toArray()));
        self::assertEquals($this->parkingId, $parkings->toArray()[1]->getId());
        self::assertEquals('Parking Salesas', $parkings->toArray()[1]->getName());
        self::assertEquals(new Location(43.367, -5.849), $parkings->toArray()[1]->getLocation());
        self::assertEquals('Gora Aparcamientos', $parkings->toArray()[0]->getName());
        self::assertEquals(new Location(43.111, -5.919), $parkings->toArray()[0]->getLocation());
    }
}
