<?php

namespace App\Service;

use App\Document\Spot;
use App\Document\Stay;
use App\Document\Vehicle;
use Doctrine\ODM\MongoDB\DocumentManager;

class StayService
{
    private DocumentManager $documentManager;
    private SpotService $spotService;

    public function __construct(DocumentManager $documentManager, SpotService $spotService)
    {
        $this->documentManager = $documentManager;
        $this->spotService = $spotService;
    }

    public function beginStay(string $parkingId, Vehicle $vehicle): Stay
    {
        $spot = $this->spotService->occupySpot($parkingId);

        return $this->create($spot, $vehicle);
    }

    public function beginStayFromBooking(Spot $spot, Vehicle $vehicle): Stay
    {
        $spot = $this->spotService->occupyBookedSpot($spot->getCode(), $spot->getParking()->getId());

        return $this->create($spot, $vehicle);
    }

    private function create(Spot $spot, Vehicle $vehicle)
    {
        $stay = new Stay();
        $stay->setStart(new \DateTime('now', new \DateTimeZone('Europe/Madrid')));
        $stay->setSpot($spot);
        $stay->setVehicle($vehicle);
        $this->documentManager->persist($stay);
        $this->documentManager->flush();

        return $stay;
    }

    public function endStay(string $id): Stay
    {
        /** @var Stay $stay */
        $stay = $this->documentManager->getRepository(Stay::class)->find($id);
        $this->spotService->freeSpot($stay->getSpot()->getCode(), $stay->getSpot()->getParking()->getId());
        $stay->setEnd(new \DateTime('now', new \DateTimeZone('Europe/Madrid')));
        $stay->calculatePrice();
        $this->documentManager->flush();

        return $stay;
    }
}
