<?php

namespace App\Service;

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
}
