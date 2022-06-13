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

    private const TIMEZONE = 'Europe/Madrid';

    public function __construct(DocumentManager $documentManager, SpotService $spotService)
    {
        $this->documentManager = $documentManager;
        $this->spotService = $spotService;
    }

    public function beginStay(string $parkingId, Vehicle $vehicle): Stay
    {
        $spot = $this->spotService->occupyFreeSpot($parkingId);

        return $this->create($spot, $vehicle);
    }

    public function beginStayFromBooking(Spot $spot, Vehicle $vehicle): Stay
    {
        $spot = $this->spotService->occupySpot($spot->getCode(), $spot->getParking()->getId());

        return $this->create($spot, $vehicle);
    }

    private function create(Spot $spot, Vehicle $vehicle)
    {
        $stay = new Stay();
        $stay->setStart(new \DateTime('now', new \DateTimeZone(self::TIMEZONE)));
        $stay->setSpot($spot);
        $stay->setVehicle($vehicle);
        $this->documentManager->persist($stay);
        $this->documentManager->flush();

        return $stay;
    }

    public function endStay(Stay $stay): Stay
    {
        $this->spotService->freeSpot($stay->getSpot()->getCode(), $stay->getSpot()->getParking()->getId());
        $stay->setEnd(new \DateTime('now', new \DateTimeZone(self::TIMEZONE)));
        $stay->calculatePrice();
        $this->documentManager->flush();

        return $stay;
    }

    public function existsActiveStay(string $parkingId, Vehicle $vehicle): bool
    {
        $spots = $this->spotService->findByParking($parkingId);
        $now = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $queryBuilder = $this->documentManager->createQueryBuilder(Stay::class);
        $queryBuilder->field('spot')->in($spots)
            ->field('vehicle')->equals($vehicle)
            ->field('start')->lte($now)
            ->field('end')->exists(false)
            ->limit(1);
        $query = $queryBuilder->getQuery();

        return null != $query->getSingleResult();
    }

    public function get(string $stayId)
    {
        return $this->documentManager->getRepository(Stay::class)->find($stayId);
    }

    public function resumeStay(Stay $stay): Stay
    {
        $this->spotService->occupySpot($stay->getSpot()->getCode(), $stay->getSpot()->getParking()->getId());
        $queryBuilder = $this->documentManager->createQueryBuilder(Stay::class);
        $queryBuilder->updateOne()
            ->field('id')->equals($stay->getId())
            ->field('end')->unsetField()
            ->getQuery()
            ->execute();
        $stay->setPrice(0.0);
        $this->documentManager->flush();

        return $stay;
    }

    public function findActiveStayByUserVehicles(array $vehicles)
    {
        $now = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $queryBuilder = $this->documentManager->createQueryBuilder(Stay::class);
        $queryBuilder->field('vehicle')->in($vehicles)
            ->field('start')->lte($now)
            ->field('end')->exists(false)
            ->limit(1);
        $query = $queryBuilder->getQuery();

        return $query->getSingleResult();
    }
}
