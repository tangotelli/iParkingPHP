<?php

namespace App\Tests;

use Faker\Generator as FakerGeneratorAlias;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseWebTestCase extends WebTestCase
{
    protected static KernelBrowser $client;
    protected static FakerGeneratorAlias $faker;

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$booted) {
            self::$client = static::createClient([], ['HTTPS' => true]);
        }
    }
}
