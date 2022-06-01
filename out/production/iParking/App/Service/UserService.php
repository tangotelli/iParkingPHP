<?php

namespace App\Service;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private DocumentManager $documentManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(DocumentManager $documentManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->documentManager = $documentManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function signin(User $user)
    {
        $plainPassword = $user->getPassword();
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->documentManager->persist($user);
        $this->documentManager->flush();
    }

    public function login(User $user, string $password)
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function findByEmail(string $email)
    {
        return $this->documentManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
