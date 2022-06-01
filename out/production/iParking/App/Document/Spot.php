<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JsonSerializable;

/**
 * @MongoDB\Document(db="iparking", collection="spots")
 */
class Spot implements JsonSerializable
{
    /**
     * @MongoDB\Id(strategy="UUID", type="string")
     */
    private string $id;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $code;
    /**
     * @MongoDB\ReferenceOne(targetDocument=Parking::class, storeAs="id")
     */
    private Parking $parking;

    /**
     * @MongoDB\Field(type="spot_status")
     */
    private Status $status;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getParking(): Parking
    {
        return $this->parking;
    }

    public function setParking(Parking $parking): void
    {
        $this->parking = $parking;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function getBookingFare(): float
    {
        return $this->parking->getBookingFare();
    }

    public function getStayFare(): float
    {
        return $this->parking->getStayFare();
    }

    public function jsonSerialize(): array
    {
        return [
            'Id' => $this->getId(),
            'Code' => $this->getCode(),
            'Parking Id' => $this->getParking()->getId(),
            'Parking' => $this->getParking()->getName(),
            'Status' => $this->getStatus()->getValue(),
        ];
    }
}
