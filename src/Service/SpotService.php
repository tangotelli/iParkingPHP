<?php

namespace App\Service;

use App\Document\Parking;
use App\Document\Spot;
use App\Document\Status;
use Doctrine\ODM\MongoDB\DocumentManager;

class SpotService
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function create(Parking $parking, string $spotCode): Spot
    {
        $spot = new Spot();
        $spot->setCode($spotCode);
        $spot->setParking($parking);
        $spot->setStatus(Status::FREE());
        $this->documentManager->persist($spot);
        $this->documentManager->flush();

        return $spot;
    }

    public function exists(string $spotCode, string $parkingId): bool
    {
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => $spotCode, 'parking' => $parkingId]);

        return null != $spot;
    }

    public function bookSpot(string $parkingId): bool
    {
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['parking' => $parkingId, 'status' => Status::FREE()]);
        if (null != $spot) {
            $spot->setStatus(Status::BOOKED());
            $this->documentManager->flush();
            return true;
        }

        return false;
    }
}
