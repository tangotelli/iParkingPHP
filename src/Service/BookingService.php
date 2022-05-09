<?php

namespace App\Service;

use App\Document\Booking;
use App\Document\Spot;
use App\Document\Vehicle;
use DateInterval;
use Doctrine\ODM\MongoDB\DocumentManager;

class BookingService
{
    private DocumentManager $documentManager;
    private SpotService $spotService;

    public function __construct(DocumentManager $documentManager, SpotService $spotService)
    {
        $this->documentManager = $documentManager;
        $this->spotService = $spotService;
    }

    public function bookSpot(string $parkingId, Vehicle $vehicle): Booking
    {
        $spot = $this->spotService->bookSpot($parkingId);
        return $this->create($spot, $vehicle);
    }

    public function anySpotFree(string $parkingId): bool
    {
        return $this->spotService->anySpotFree($parkingId);
    }

    private function create(Spot $spot, Vehicle $vehicle): Booking
    {
        $booking = new Booking();
        $start = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $end = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $interval = new DateInterval('PT1H');
        $end->add($interval);
        $booking->setStart($start);
        $booking->setEnd($end);
        $booking->setSpot($spot);
        $booking->setVehicle($vehicle);
        $booking->calculatePrice();
        $this->documentManager->persist($booking);
        $this->documentManager->flush();

        return $booking;
    }
}
