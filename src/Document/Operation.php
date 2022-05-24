<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JsonSerializable;

/**
 * @MongoDB\MappedSuperclass
 */
abstract class Operation implements JsonSerializable
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
    protected \DateTime $end;
    /**
     * @MongoDB\ReferenceOne(targetDocument=Spot::class, storeAs="id")
     */
    private Spot $spot;
    /**
     * @MongoDB\ReferenceOne(targetDocument=Vehicle::class, storeAs="id")
     */
    private Vehicle $vehicle;
    protected float $price;

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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    abstract public function calculatePrice(): void;
}
