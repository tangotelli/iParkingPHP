<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JsonSerializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @MongoDB\Document(db="iparking", collection="users")
 */
class User implements PasswordAuthenticatedUserInterface, JsonSerializable
{
    /**
     * @MongoDB\Id(strategy="UUID", type="string")
     */
    private string $id;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $email;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $name;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $password;

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function jsonSerialize(): array
    {
        return [
            'Id' => $this->getId(),
            'Email' => $this->getEmail(),
            'Name' => $this->getName(),
        ];
    }
}
