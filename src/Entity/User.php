<?php
declare(strict_types=1);

namespace App\Entity;

final class User
{
    private ?int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $passwordHash;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $passwordHash,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string  { return $this->lastName; }
    public function getEmail(): string     { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}
