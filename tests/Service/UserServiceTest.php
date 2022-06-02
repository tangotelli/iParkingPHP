<?php

namespace App\Tests\Service;

use App\Document\User;
use App\Service\UserService;

class UserServiceTest extends \App\Tests\BaseIntegrationTestCase
{
    private UserService $userService;
    private string $userId;
    private string $plainPassword = 'oat829tao';

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = $this->kernelInterface->getContainer()
            ->get(UserService::class);
        $user = new User();
        $user->setName('Dummy');
        $user->setEmail('dummy@hotmail.com');
        $user->setPassword($this->plainPassword);
        $this->userService->signin($user);
        $this->userId = $user->getId();
    }

    public function testSignin()
    {
        $user = new User();
        $user->setName('Cobaya');
        $user->setEmail('cobaya21@mailing.eu.com');
        $plainPassword = 'cobaya21';
        $user->setPassword($plainPassword);
        $this->userService->signin($user);
        self::assertNotNull($user->getId());
        self::assertNotEquals($plainPassword, $user->getPassword());
    }

    public function testLogin()
    {
        /** @var User $user */
        $user = $this->documentManager->getRepository(User::class)->find($this->userId);
        self::assertTrue($this->userService->login($user, $this->plainPassword));
        self::assertNotEquals($this->plainPassword, $user->getPassword());
        self::assertFalse($this->userService->login($user, $user->getPassword()));
    }

    public function testFindByEmail() {
        /** @var User $user */
        $user = $this->userService->findByEmail('dummy@hotmail.com');
        self::assertNotNull($user);
        self::assertEquals('Dummy', $user->getName());
        self::assertEquals($this->userId, $user->getId());
        $user = $this->userService->findByEmail('cobaya21@mailing.eu.com');
        self::assertNull($user);
    }
}
