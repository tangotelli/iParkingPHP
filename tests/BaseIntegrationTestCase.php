<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseIntegrationTestCase extends KernelTestCase
{
    protected DocumentManager $documentManager;
    protected KernelInterface $kernelInterface;

    protected function setUp(): void
    {
        $this->kernelInterface = self::bootKernel();
        $this->documentManager = $this->kernelInterface->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
        $purger = new MongoDBPurger($this->documentManager);
        $purger->purge();
    }
}
