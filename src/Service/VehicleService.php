<?php

namespace App\Service;

use App\Document\User;
use App\Document\Vehicle;
use Doctrine\ODM\MongoDB\DocumentManager;

class VehicleService
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function findByUserAndLicensePlate(User $user, string $licensePlate)
    {
        return $this->documentManager->getRepository(Vehicle::class)
            ->findOneBy(['user' => $user, 'licensePlate' => $licensePlate]);
    }

    public function register(Vehicle $vehicle)
    {
        $this->documentManager->persist($vehicle);
        $this->documentManager->flush();
    }

    public function findByUserAndNickname(User $user, string $nickname)
    {
        return $this->documentManager->getRepository(Vehicle::class)
            ->findOneBy(['user' => $user, 'nickname' => $nickname]);
    }

    public function findByUser(User $user)
    {
        return $this->documentManager->getRepository(Vehicle::class)
            ->findBy(['user' => $user]);
    }
}
