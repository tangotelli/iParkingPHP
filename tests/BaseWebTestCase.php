<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseWebTestCase extends WebTestCase
{
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        self::$client = static::createClient([], ['HTTPS' => true]);
    }
}
