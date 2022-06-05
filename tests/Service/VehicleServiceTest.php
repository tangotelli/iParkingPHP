<?php

namespace App\Tests\Service;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Tests\BaseIntegrationTestCase;

class VehicleServiceTest extends BaseIntegrationTestCase
{
    private VehicleService $vehicleService;
    private UserService $userService;
    private User $userA;
    private User $userB;
    private Vehicle $vehicleA;
    private Vehicle $vehicleB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vehicleService = $this->getContainer()
            ->get(VehicleService::class);
        $this->userService = $this->getContainer()
            ->get(UserService::class);
        $this->persistUsers();
        $this->persistVehicles();
    }

    private function persistUsers(): void
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
        $this->vehicleB->setNickname('Coche Primo');
        $this->vehicleB->setLicensePlate('O 0231 BG');
        $this->vehicleB->setUser($this->userA);
        $this->documentManager->persist($this->vehicleB);
        $this->documentManager->flush();
    }

    public function testFindByUserAndLicensePlate()
    {
        self::assertEquals($this->vehicleA,
            $this->vehicleService->findByUserAndLicensePlate($this->userA, '4657 FGT'));
        self::assertNull($this->vehicleService->findByUserAndLicensePlate($this->userB, '4657 FGT'));
        self::assertNull($this->vehicleService->findByUserAndLicensePlate($this->userA, '44657 FGT'));
    }

    public function testRegister()
    {
        $vehicle = new Vehicle();
        $vehicle->setNickname('Fiat Tipo');
        $vehicle->setLicensePlate('2321 BGT');
        $vehicle->setUser($this->userB);
        $this->vehicleService->register($vehicle);
        self::assertNotNull($vehicle->getId());
    }

    public function testFindByUserAndNickname()
    {
        self::assertEquals($this->vehicleA,
            $this->vehicleService->findByUserAndNickname($this->userA, 'Seat Ibiza'));
        self::assertNull($this->vehicleService->findByUserAndNickname($this->userB, 'Seat Ibiza'));
        self::assertNull($this->vehicleService->findByUserAndNickname($this->userA, '4657 FGT'));
    }

    public function testFind()
    {
        self::assertEquals($this->vehicleA, $this->vehicleService->find($this->vehicleA->getId()));
        self::assertNull($this->vehicleService->find('randomId'));
    }

    public function testDelete()
    {
        self::assertEquals($this->vehicleA, $this->vehicleService->find($this->vehicleA->getId()));
        $this->vehicleService->delete($this->vehicleA);
        self::assertNull($this->vehicleService->find($this->vehicleA->getId()));
    }
}
