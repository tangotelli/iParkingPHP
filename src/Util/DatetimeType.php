<?php

namespace App\Util;

use Doctrine\ODM\MongoDB\Types\ClosureToPHP;
use Doctrine\ODM\MongoDB\Types\Type;
use MongoDB\BSON\UTCDateTime;

class DatetimeType extends Type
{
    use ClosureToPHP;

    public function convertToPHPValue($value): \DateTime
    {
        return new \DateTime('@'.$value->sec, new \DateTimeZone('Europe/Madrid'));
    }

    public function convertToDatabaseValue($value): UTCDateTime
    {
        return new UTCDateTime($value);
    }
}
