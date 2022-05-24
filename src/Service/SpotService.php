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

    public function anySpotFree(string $parkingId): bool
    {
        /** @var Spot $spot */
        $spot = $this->findFreeSpot($parkingId);
        if (null != $spot) {
            return true;
        } else {
            return false;
        }
    }

    public function bookSpot(string $parkingId): Spot
    {
        /** @var Spot $spot */
        $spot = $this->findFreeSpot($parkingId);
        $spot->setStatus(Status::BOOKED());
        $this->documentManager->flush();

        return $spot;
    }

    public function freeSpot(string $spotCode, string $parkingId)
    {
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => $spotCode, 'parking' => $parkingId]);
        $spot->setStatus(Status::FREE());
        $this->documentManager->flush();
    }

    public function occupySpot(string $parkingId): Spot
    {
        /** @var Spot $spot */
        $spot = $this->findFreeSpot($parkingId);
        $spot->setStatus(Status::OCCUPIED());
        $this->documentManager->flush();

        return $spot;
    }

    private function findFreeSpot(string $parkingId)
    {
        return $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['parking' => $parkingId, 'status' => Status::FREE()]);
    }

    public function occupyBookedSpot(string $spotCode, string $parkingId): Spot
    {
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => $spotCode, 'parking' => $parkingId]);
        $spot->setStatus(Status::OCCUPIED());
        $this->documentManager->flush();

        return $spot;
    }
}
