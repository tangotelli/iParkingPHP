<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Faker\Factory as FakerFactoryAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends BaseWebTestCase
{
    private static array $body;

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactoryAlias::create('es_ES');
        self::$body = [
            'email' => self::$faker->email(),
            'name' => self::$faker->name(),
            'password' => self::$faker->word(),
        ];
    }

    public function testSigninSuccessful()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/user/signin',
            [],
            [],
            [],
            strval(json_encode(self::$body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $user = json_decode(strval($response->getContent()), true);
        self::assertNotNull($user['Id']);
        self::assertEquals(self::$body['email'], $user['Email']);
        self::assertEquals(self::$body['name'], $user['Name']);
    }

    /**
     * @depends testSigninSuccessful
     */
    public function testSigninUnsuccesful()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/user/signin',
            [],
            [],
            [],
            strval(json_encode(self::$body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @depends testSigninSuccessful
     */
    public function testLoginSuccesful()
    {
        $query = '?email='.self::$body['email'].'&password='.self::$body['password'];
        self::$client->request(
            Request::METHOD_GET,
            '/user/login'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $user = json_decode(strval($response->getContent()), true);
        self::assertNotNull($user['Id']);
        self::assertEquals(self::$body['email'], $user['Email']);
        self::assertEquals(self::$body['name'], $user['Name']);
    }

    /**
     * @depends testSigninSuccessful
     */
    public function testLoginUnsuccesfulWrongEmail()
    {
        $query = '?email='.self::$body['email'].'mal&password='.self::$body['password'];
        self::$client->request(
            Request::METHOD_GET,
            '/user/login'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @depends testSigninSuccessful
     */
    public function testLoginUnsuccesfulWrongPassword()
    {
        $query = '?email='.self::$body['email'].'&password='.self::$body['password'].'mal';
        self::$client->request(
            Request::METHOD_GET,
            '/user/login'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public static function tearDownAfterClass(): void
    {
        $documentManager = self::getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
        $purger = new MongoDBPurger($documentManager);
        $purger->purge();
    }
}
