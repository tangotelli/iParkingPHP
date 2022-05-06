<?php

namespace App\Util;

use App\Document\Status;

class StatusGenerator
{
    public static function free(): Status
    {
        return new Status('FREE');
    }

    public static function booked(): Status
    {
        return new Status('BOOKED');
    }

    public static function occupied(): Status
    {
        return new Status('OCCUPIED');
    }
}
