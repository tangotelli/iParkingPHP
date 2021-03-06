<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JsonSerializable;

/**
 * @MongoDB\Document(collection="vehicles")
 */
class Vehicle implements JsonSerializable
{
    /**
     * @MongoDB\Id(strategy="UUID", type="string")
     */
    private string $id;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $nickname;
    /**
     * @MongoDB\Field(type="string")
     */
    private string $licensePlate;
    /**
     * @MongoDB\ReferenceOne(targetDocument=User::class, storeAs="id")
     */
    private User $user;

    public function getId(): string
    {
        return $this->id;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getLicensePlate(): string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(string $licensePlate): void
    {
        $this->licensePlate = $licensePlate;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function jsonSerialize(): array
    {
        return [
            'Id' => $this->getId(),
            'Nickname' => $this->getNickname(),
            'License Plate' => $this->getLicensePlate(),
            'User' => $this->getUser()->getEmail(),
        ];
    }
}
