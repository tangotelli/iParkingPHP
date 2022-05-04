<?php

namespace App\Document;

/**
 * @MongoDB\EmbeddedDocument()
 */
class Location
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
}