<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(db="iparking", collection="stays")
 */
class Stay extends Operation
{
    public function calculatePrice(): void
    {
        $this->setPrice($this->minutesPassed() * $this->getSpot()->getStayFare());
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
            /*'End' => null != $this->end ? $this->getEnd()->format('d/m/Y H:i:s') : 'null',
            'Price' => null != $this->price ? $this->getPrice() : 'null',*/
        ];
    }

    private function minutesPassed(): float|int
    {
        return ($this->getStart()->getTimestamp() - $this->getEnd()->getTimestamp()) / 60;
    }
}
