<?php

namespace App\Tests;

use Faker\Factory as FakerFactoryAlias;
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
        self::$client = static::createClient([], ['HTTPS' => true]);
        self::$faker = FakerFactoryAlias::create('es_ES');
    }
}
