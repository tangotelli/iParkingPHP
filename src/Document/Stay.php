<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="stays")
 */
class Stay extends Operation
{
    public function calculatePrice(): void
    {
        $this->setPrice(abs($this->minutesPassed() * $this->getSpot()->getStayFare()));
    }

    public function jsonSerialize()
    {
        return [
            'Id' => $this->getId(),
            'Parking Id' => $this->getSpot()->getParking()->getId(),
            'Parking' => $this->getSpot()->getParking()->getName(),
            'Spot' => $this->getSpot()->getCode(),
            'Vehicle' => $this->getVehicle()->getNickname(),
            'User' => $this->getVehicle()->getUser()->getEmail(),
            'Beginning' => $this->getStart()->format('d/m/Y H:i:s'),
            'End' => $this->start != $this->end ? $this->getEnd()->format('d/m/Y H:i:s') : 'null',
            'Price' => null != $this->price ? $this->getPrice() : 'null',
            'Fare' => $this->getSpot()->getParking()->getStayFare(),
        ];
    }

    private function minutesPassed(): float|int
    {
        return floor(($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp()) / 60);
    }
}
