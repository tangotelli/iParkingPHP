<?php

namespace App\Tests\Controller;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Tests\BaseWebTestCase;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Faker\Factory as FakerFactoryAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VehicleControllerTest extends BaseWebTestCase
{
    private static array $body;
    private string $vehicleId;

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactoryAlias::create('es_ES');
        self::$body = [
            'email' => self::$faker->email(),
            'nickname' => self::$faker->word(),
            'licensePlate' => self::$faker->word(),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->persistUser();
    }

    private function persistUser()
    {
        $user = new User();
        $user->setName('DummyVCT');
        $user->setEmail(self::$body['email']);
        $user->setPassword('password');
        $userService = $this->getContainer()->get(UserService::class);
        $userService->signin($user);
    }

    public function testRegisterSuccessful()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/vehicle/register',
            [],
            [],
            [],
            strval(json_encode(self::$body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $vehicle = json_decode(strval($response->getContent()), true);
        self::assertNotNull($vehicle['Id']);
        self::assertEquals(self::$body['email'], $vehicle['User']);
        self::assertEquals(self::$body['nickname'], $vehicle['Nickname']);
        self::assertEquals(self::$body['licensePlate'], $vehicle['License Plate']);
    }

    public function testRegisterUnsuccessfulUnregisteredUser()
    {
        $alternateBody = [
            'email' => self::$body['email'].'mal',
            'nickname' => self::$faker->word(),
            'licensePlate' => self::$faker->word(),
        ];
        self::$client->request(
            Request::METHOD_POST,
            '/vehicle/register',
            [],
            [],
            [],
            strval(json_encode($alternateBody))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testRegisterSuccessful
     */
    public function testRegisterUnsuccessfulRepeatedVehicle()
    {
        self::$client->request(
            Request::METHOD_POST,
            '/vehicle/register',
            [],
            [],
            [],
            strval(json_encode(self::$body))
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @depends testRegisterSuccessful
     */
    public function testFindByUserAndNicknameSuccessful()
    {
        $query = '?email='.self::$body['email'].'&nickname='.self::$body['nickname'];
        self::$client->request(
            Request::METHOD_GET,
            '/vehicle/get'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $vehicle = json_decode(strval($response->getContent()), true);
        self::assertNotNull($vehicle['Id']);
        self::assertEquals(self::$body['email'], $vehicle['User']);
        self::assertEquals(self::$body['nickname'], $vehicle['Nickname']);
        self::assertEquals(self::$body['licensePlate'], $vehicle['License Plate']);
    }

    public function testFindByUserAndNicknameSuccesfulUnregisteredUser()
    {
        $query = '?email='.self::$body['email'].'mal&nickname='.self::$body['nickname'];
        self::$client->request(
            Request::METHOD_GET,
            '/vehicle/get'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testFindByUserAndNicknameSuccesfulUnregisteredVehicle()
    {
        $query = '?email='.self::$body['email'].'&nickname=mql'.self::$body['nickname'];
        self::$client->request(
            Request::METHOD_GET,
            '/vehicle/get'.$query
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @depends testRegisterSuccessful
     */
    public function testFindByUserSuccessful()
    {
        self::$client->request(
            Request::METHOD_GET,
            '/vehicle/get/'.self::$body['email']
        );
        $response = self::$client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
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
