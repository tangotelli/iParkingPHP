<?php

namespace App\Service;

use App\Document\Booking;
use App\Document\Spot;
use App\Document\User;
use App\Document\Vehicle;
use DateInterval;
use Doctrine\ODM\MongoDB\DocumentManager;

class BookingService
{
    private DocumentManager $documentManager;
    private SpotService $spotService;
    private VehicleService $vehicleService;

    private const TIMEZONE = 'Europe/Madrid';

    public function __construct(DocumentManager $documentManager, SpotService $spotService,
                                VehicleService $vehicleService)
    {
        $this->documentManager = $documentManager;
        $this->spotService = $spotService;
        $this->vehicleService = $vehicleService;
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
        $start = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $end = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
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

    public function findActiveBooking(User $user, string $parkingId)
    {
        $vehicles = $this->vehicleService->findByUser($user);
        if (null == $vehicles) {
            return null;
        }
        $spots = $this->spotService->findByParking($parkingId);
        $now = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $queryBuilder = $this->documentManager->createQueryBuilder(Booking::class);
        $queryBuilder->field('vehicle')->in($vehicles)
                     ->field('spot')->in($spots)
                     ->field('start')->lte($now)
                     ->field('end')->gte($now)
                     ->limit(1);
        $query = $queryBuilder->getQuery();

        return $query->getSingleResult();
    }

    public function existsActiveBooking(mixed $parkingId, Vehicle $vehicle): bool
    {
        $spots = $this->spotService->findByParking($parkingId);
        $now = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $queryBuilder = $this->documentManager->createQueryBuilder(Booking::class);
        $queryBuilder->field('spot')->in($spots)
            ->field('vehicle')->equals($vehicle)
            ->field('start')->lte($now)
            ->field('end')->gte($now)
            ->limit(1);
        $query = $queryBuilder->getQuery();
        if (null != $query->getSingleResult()) {
            return true;
        } else {
            return false;
        }
    }
}
