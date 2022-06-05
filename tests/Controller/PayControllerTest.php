<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use App\Util\RandomIntegerGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PayControllerTest extends BaseWebTestCase
{

    private RandomIntegerGenerator|MockObject $generatorMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generatorMock = $this->createMock(RandomIntegerGenerator::class);
    }

    public function testPaySuccesful()
    {
        $this->generatorMock->method('generate')->willReturn(5);
        $this->getContainer()
            ->set('App\Util\RandomIntegerGenerator', $this->generatorMock);
        self::$client->request(
            Request::METHOD_POST,
            '/payment/pay'
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPayUnsuccesful()
    {
        $this->generatorMock->method('generate')->willReturn(3);
        $this->getContainer()
            ->set('App\Util\RandomIntegerGenerator', $this->generatorMock);
        self::$client->request(
            Request::METHOD_POST,
            '/payment/pay'
        );
        self::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
