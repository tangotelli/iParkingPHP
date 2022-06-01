<?php

namespace App\Tests\Service;

use App\Kernel;
use Doctrine\ODM\MongoDB\DocumentManager;

class ParkingServiceTest extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    private DocumentManager $documentManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testDummy()
    {
        self::assertSame(1, 1);
    }
}
