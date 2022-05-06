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
        $existingUser = $this->documentManager->getRepository(User::class)
            ->findOneBy(['email' => $user->getEmail()]);
        if (null == $existingUser) {
            $plainPassword = $user->getPassword();
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
            $this->documentManager->persist($user);
            $this->documentManager->flush();
        }
    }

    public function login(string $email, string $password): bool
    {
        /** @var User $user */
        $user = $this->documentManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        if (null != $user) {
            if ($this->passwordHasher->isPasswordValid($user, $password)) {
                return true;
            }
        }

        return false;
    }
}
