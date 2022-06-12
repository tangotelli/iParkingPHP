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

        return null != $spot;
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

    public function occupyFreeSpot(string $parkingId): Spot
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

    public function occupySpot(string $spotCode, string $parkingId): Spot
    {
        /** @var Spot $spot */
        $spot = $this->documentManager->getRepository(Spot::class)
            ->findOneBy(['code' => $spotCode, 'parking' => $parkingId]);
        $spot->setStatus(Status::OCCUPIED());
        $this->documentManager->flush();

        return $spot;
    }

    public function findByParking(string $parkingId)
    {
        return $this->documentManager->getRepository(Spot::class)
            ->findBy(['parking' => $parkingId]);
    }

    public function countFreeSpots(string $parkingId)
    {
        /*$builder = $this->documentManager->createAggregationBuilder(Spot::class);
        $builder
            ->match()
                ->field('parking')->equals($parkingId)
                ->field('status')->equals(Status::FREE())
            ->group()*/
        $queryBuilder = $this->documentManager->createQueryBuilder(Spot::class);
        $queryBuilder
            ->field('parking')->equals($parkingId)
            ->field('status')->equals(Status::FREE());

        return $queryBuilder->count()->getQuery()->execute();
    }

    public function countSpots(string $parkingId)
    {
        $queryBuilder = $this->documentManager->createQueryBuilder(Spot::class);
        $queryBuilder->field('parking')->equals($parkingId);

        return $queryBuilder->count()->getQuery()->execute();
    }
}
