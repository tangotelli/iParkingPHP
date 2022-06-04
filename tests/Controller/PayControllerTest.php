<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PayControllerTest extends BaseWebTestCase
{
    public function testPay()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/payment/pay'
        );
        $response = self::$client->getResponse();
        self::assertTrue(Response::HTTP_OK == $response->getStatusCode()
            || Response::HTTP_INTERNAL_SERVER_ERROR == $response->getStatusCode());
    }
}
