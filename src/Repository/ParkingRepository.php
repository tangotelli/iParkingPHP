<?php

namespace App\Repository;

use App\Document\Parking;
use Doctrine\ODM\MongoDB\DocumentManager;

class ParkingRepository
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
}
