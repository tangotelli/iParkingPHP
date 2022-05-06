<?php

namespace App\Document;

use MyCLabs\Enum\Enum;

final class Status extends Enum
{
    private const FREE = 'Free';
    private const BOOKED = 'Booked';
    private const OCCUPIED = 'Occupied';
}
