<?php

namespace App\Tests\Controller;

use App\Document\User;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends BaseWebTestCase
{
    private $body;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->persistUser();
        $this->body = [
            'email' => self::$faker->email(),
            'name' => self::$faker->name(),
            'password' => self::$faker->password(),
        ];
    }

    private function persistUser()
    {
        $user = new User();
        $user->setName('Dummy');
        $user->setEmail('dummy@hotmail.com');
        $user->setPassword('password');
        // $this->userService->signin($user);
    }

    public function testSigninSuccessful()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/user/signin',
            [],
            [],
            [],
            strval(json_encode($this->body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $user = json_decode(strval($response->getContent()), true);
        self::assertNotNull($user['Id']);
        self::assertEquals($this->body['email'], $user['Email']);
        self::assertEquals($this->body['name'], $user['Name']);
    }

    /**
     * @depends testSigninSucessful
     */
    public function testSigninUnsuccesful()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/user/signin',
            [],
            [],
            [],
            strval(json_encode($this->body))
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @depends testSigninSucessful
     */
    public function testLoginSuccesful()
    {
        $query = '?email='.$this->body['email'].'&password='.$this->body['password'];
        self::$client->request(
            Request::METHOD_GET,
            '/user/login'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $user = json_decode(strval($response->getContent()), true);
        self::assertNotNull($user['Id']);
        self::assertEquals($this->body['email'], $user['Email']);
        self::assertEquals($this->body['name'], $user['Name']);
    }

    /**
     * @depends testSigninSucessful
     */
    public function testLoginUnsuccesfulWrongEmail()
    {
        $query = '?email='.$this->body['email'].'mal&password='.$this->body['password'];
        self::$client->request(
            Request::METHOD_GET,
            '/user/login'.$query
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @depends testSigninSucessful
     */
    public function testLoginUnsuccesfulWrongPassword()
    {
        $query = '?email='.$this->body['email'].'&password='.$this->body['password'].'mal';
        self::$client->request(
            Request::METHOD_GET,
            '/user/login'.$query
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
