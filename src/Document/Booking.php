<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(db="iparking", collection="bookings")
 */
class Booking extends Operation
{
    public function calculatePrice(): void
    {
        $this->setPrice($this->getSpot()->getBookingFare());
    }

    public function jsonSerialize(): array
    {
        $this->calculatePrice();
        return [
            'Id' => $this->getId(),
            'Parking Id' => $this->getSpot()->getParking()->getId(),
            'Parking' => $this->getSpot()->getParking()->getName(),
            'Spot' => $this->getSpot()->getCode(),
            'Vehicle' => $this->getVehicle()->getNickname(),
            'User' => $this->getVehicle()->getUser()->getEmail(),
            'Price' => $this->getPrice(),
        ];
    }
}
