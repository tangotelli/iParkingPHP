<?php

namespace App\Service;

use App\Document\Location;
use App\Document\Parking;
use Doctrine\ODM\MongoDB\DocumentManager;

class ParkingService
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function create(Parking $parking)
    {
        $this->documentManager->persist($parking);
        $this->documentManager->flush();
    }

    public function get(string $id)
    {
        return $this->documentManager->getRepository(Parking::class)->find($id);
    }

    public function findAll()
    {
        return $this->documentManager->getRepository(Parking::class)->findAll();
    }

    public function findClosestParkings(Location $location)
    {
        $queryBuilder = $this->documentManager->createQueryBuilder(Parking::class);
        $queryBuilder->field('location')->near($location->getLatitude(), $location->getLongitude());
        return $queryBuilder->getQuery()->execute();
    }
}
