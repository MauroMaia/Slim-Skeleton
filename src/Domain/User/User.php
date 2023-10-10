<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    private string $username;

    private string $firstName;

    private string $lastName;

    public function __construct(
        public          ?int       $id,
        string                     $username,
        string                     $firstName,
        string                     $lastName,
        public readonly string     $password,
        public readonly string|null     $recoverPassword,
        public readonly string     $email,
        public readonly string     $jobTitle,
        public readonly int        $roleId,
        public readonly ?\DateTime $createdAt = new \DateTime('now'),
        public readonly ?\DateTime $updatedAt = new \DateTime('now'))
    {
        $this->username = strtolower($username);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }


    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'createdAt' => $this->createdAt->format(\DateTime::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTime::ATOM),
        ];
    }
}
