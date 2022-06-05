<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseIntegrationTestCase extends BaseWebTestCase
{
    protected DocumentManager $documentManager;
    protected KernelInterface $kernelInterface;

    protected function setUp(): void
    {
        $this->documentManager = $this->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
        $purger = new MongoDBPurger($this->documentManager);
        $purger->purge();
    }
}
