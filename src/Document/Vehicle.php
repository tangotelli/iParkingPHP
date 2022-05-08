<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(db="iparking", collection="vehicles")
 */
class Vehicle
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
}
