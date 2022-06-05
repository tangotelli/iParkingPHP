<?php

namespace App\Util;

class RandomIntegerGenerator
{
    public function generate(): int
    {
        return random_int(1, 10);
    }
}