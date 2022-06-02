<?php

namespace App\Tests\Service;

use App\Document\Location;
use App\Document\Parking;
use App\Document\Spot;
use App\Document\Status;
use App\Service\ParkingService;
use App\Service\SpotService;

class SpotServiceTest extends \App\Tests\BaseIntegrationTestCase
{
    private SpotService $spotService;
    private ParkingService $parkingService;
    private Parking $parking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->spotService = $this->kernelInterface->getContainer()
            ->get(SpotService::class);
        $this->parkingService = $this->kernelInterface->getContainer()
            ->get(ParkingService::class);
        $this->persistParking();
        $this->persistSpots();
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
        $spotA = new Spot();
        $spotA->setCode('A1');
        $spotA->setParking($this->parking);
        $spotA->setStatus(Status::FREE());
        $this->documentManager->persist($spotA);
        $spotB = new Spot();
        $spotB->setCode('B1');
        $spotB->setParking($this->parking);
        $spotB->setStatus(Status::BOOKED());
        $this->documentManager->persist($spotB);
        $spotC = new Spot();
        $spotC->setCode('C1');
        $spotC->setParking($this->parking);
        $spotC->setStatus(Status::OCCUPIED());
        $this->documentManager->persist($spotC);
        $this->documentManager->flush();
    }

    public function testCreate()
    {
        $spot = $this->spotService->create($this->parking, 'D1');
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('D1', $spot->getCode());
        self::assertEquals(Status::FREE(), $spot->getStatus());
    }

    public function testExists()
    {
        self::assertTrue($this->spotService->exists('A1', $this->parking->getId()));
        self::assertFalse($this->spotService->exists('A1', 'randomId'));
        self::assertFalse($this->spotService->exists('beta', $this->parking->getId()));
    }

    public function testAnySpotFree()
    {
        self::assertTrue($this->spotService->anySpotFree($this->parking->getId()));
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => 'A1', 'parking' => $this->parking->getId()]);
        $spot->setStatus(Status::BOOKED());
        $this->documentManager->flush();
        self::assertFalse($this->spotService->anySpotFree($this->parking->getId()));
    }

    public function testBookSpot()
    {
        /** @var Spot $spot */
        $spot = $this->spotService->bookSpot($this->parking->getId());
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::BOOKED(), $spot->getStatus());
    }

    public function testFreeSpot()
    {
        $this->spotService->freeSpot('B1', $this->parking->getId());
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => 'B1', 'parking' => $this->parking->getId()]);
        self::assertNotNull($spot);
        self::assertEquals('B1', $spot->getCode());
        self::assertEquals(Status::FREE(), $spot->getStatus());
        $this->spotService->freeSpot('C1', $this->parking->getId());
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => 'C1', 'parking' => $this->parking->getId()]);
        self::assertNotNull($spot);
        self::assertEquals('C1', $spot->getCode());
        self::assertEquals(Status::FREE(), $spot->getStatus());
    }

    public function testBookAndFreeSpot()
    {
        /** @var Spot $spot */
        $spot = $this->spotService->bookSpot($this->parking->getId());
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::BOOKED(), $spot->getStatus());
        $this->spotService->freeSpot('A1', $this->parking->getId());
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => 'A1', 'parking' => $this->parking->getId()]);
        self::assertNotNull($spot);
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::FREE(), $spot->getStatus());
    }

    public function testOccupyFreeSpot()
    {
        /** @var Spot $spot */
        $spot = $this->spotService->occupyFreeSpot($this->parking->getId());
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::OCCUPIED(), $spot->getStatus());
    }

    public function testOccupyAndFreeSpot()
    {
        /** @var Spot $spot */
        $spot = $this->spotService->occupyFreeSpot($this->parking->getId());
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::OCCUPIED(), $spot->getStatus());
        $this->spotService->freeSpot('A1', $this->parking->getId());
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => 'A1', 'parking' => $this->parking->getId()]);
        self::assertNotNull($spot);
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::FREE(), $spot->getStatus());
    }

    public function testOccupySpot()
    {
        /** @var Spot $spot */
        $spot = $this->spotService->occupySpot('A1', $this->parking->getId());
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('A1', $spot->getCode());
        self::assertEquals(Status::OCCUPIED(), $spot->getStatus());
        $spot = $this->spotService->occupySpot('B1', $this->parking->getId());
        self::assertNotNull($spot);
        self::assertNotNull($spot->getId());
        self::assertEquals($this->parking, $spot->getParking());
        self::assertEquals('B1', $spot->getCode());
        self::assertEquals(Status::OCCUPIED(), $spot->getStatus());
    }

    public function testFindByParking()
    {
        $spots = $this->spotService->findByParking($this->parking->getId());
        self::assertEquals(3, count($spots));
        self::assertEquals('A1', $spots[0]->getCode());
        self::assertEquals(Status::FREE(), $spots[0]->getStatus());
        self::assertEquals('B1', $spots[1]->getCode());
        self::assertEquals(Status::BOOKED(), $spots[1]->getStatus());
        self::assertEquals('C1', $spots[2]->getCode());
        self::assertEquals(Status::OCCUPIED(), $spots[2]->getStatus());
    }
}
