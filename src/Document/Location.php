<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JsonSerializable;

/**
 * @MongoDB\EmbeddedDocument()
 */
class Location implements JsonSerializable
{
    /**
     * @MongoDB\Field(type="float")
     */
    private float $latitude;
    /**
     * @MongoDB\Field(type="float")
     */
    private float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function jsonSerialize()
    {
        return [
            'Latitude' => $this->getLatitude(),
            'Longitude' => $this->getLongitude(),
        ];
    }
}