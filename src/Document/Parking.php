<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JsonSerializable;

/**
 * @MongoDB\Document(collection="parkings")
 * @MongoDB\Index(keys={"location"="2d"})
 */
class Parking implements JsonSerializable
{
    /**
     * @MongoDB\Id(strategy="UUID", type="string")
     */
    private string $id;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $name;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $address;
    /**
     * @MongoDB\Field(type="float")
     */
    private float $bookingFare;
    /**
     * @MongoDB\Field(type="float")
     */
    private float $stayFare;
    /**
     * @MongoDB\EmbedOne(targetDocument=Location::class)
     */
    private Location $location;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getBookingFare(): float
    {
        return $this->bookingFare;
    }

    public function setBookingFare(float $bookingFare): void
    {
        $this->bookingFare = $bookingFare;
    }

    public function getStayFare(): float
    {
        return $this->stayFare;
    }

    public function setStayFare(float $stayFare): void
    {
        $this->stayFare = $stayFare;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function jsonSerialize()
    {
        return [
            'Id' => $this->getId(),
            'Name' => $this->getName(),
            'Address' => $this->getAddress(),
            'Booking Fare' => $this->getBookingFare(),
            'Stay Fare' => $this->getStayFare(),
            'Location' => $this->getLocation()->jsonSerialize(),
        ];
    }
}
