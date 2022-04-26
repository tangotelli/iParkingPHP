<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class dummyTest extends TestCase
{
    public function testConstructor(): void
    {
        $num = 1;
        self::assertSame($num, 2);
    }
}