<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\MappedSuperclass
 */
abstract class Operation
{
    /**
     * @MongoDB\Id(strategy="UUID", type="string")
     */
    private string $id;
    /**
     * @MongoDB\Field(type="my_datetime")
     */
    private \DateTime $start;
    /**
     * @MongoDB\Field(type="my_datetime")
     */
    private \DateTime $end;
    /**
     * @MongoDB\ReferenceOne(targetDocument=Spot::class, storeAs="id")
     */
    private Spot $spot;
    /**
     * @MongoDB\ReferenceOne(targetDocument=Vehicle::class, storeAs="id")
     */
    private Vehicle $vehicle;

    public function getId(): string
    {
        return $this->id;
    }

    public function getStart(): \DateTime
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end): void
    {
        $this->end = $end;
    }

    public function getSpot(): Spot
    {
        return $this->spot;
    }

    public function setSpot(Spot $spot): void
    {
        $this->spot = $spot;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }

    abstract public function calculatePrice(): float;
}
