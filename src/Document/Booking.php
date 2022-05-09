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
}
