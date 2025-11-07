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
  private string $role = 'user';
  private ?string $mfaMethod = null;
  private ?string $mfaSecret = null;

  // 🔹 Nouvelles propriétés pour le refresh token
  private ?string $refreshToken = null;
  private ?string $refreshTokenExpiresAt = null;

  public function __construct(
    string $firstName,
    string $lastName,
    string $email,
    string $passwordHash,
    ?int $id = null,
    ?string $role = null,
    ?string $refreshToken = null,
    ?string $refreshTokenExpiresAt = null,
    ?string $mfaMethod = null,
    ?string $mfaSecret = null

  ) {
    $this->id = $id;
    $this->firstName = $firstName;
    $this->lastName  = $lastName;
    $this->email = $email;
    $this->passwordHash = $passwordHash;
    $this->mfaMethod = $mfaMethod;
    $this->mfaSecret = $mfaSecret;

    if ($role !== null) {
      $this->role = $role;
    }

    $this->refreshToken = $refreshToken;
    $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
  }

  public function getId(): ?int
  {
    return $this->id;
  }
  public function setId(int $id): void
  {
    $this->id = $id;
  }
  public function getFirstName(): string
  {
    return $this->firstName;
  }
  public function getLastName(): string
  {
    return $this->lastName;
  }
  public function getEmail(): string
  {
    return $this->email;
  }
  public function getPasswordHash(): string
  {
    return $this->passwordHash;
  }
  public function getRole(): string
  {
    return $this->role;
  }
  public function getFullName(): string
  {
    return trim($this->firstName . ' ' . $this->lastName);
  }

  // 🔹 Nouveaux getters pour le refresh token
  public function getRefreshToken(): ?string
  {
    return $this->refreshToken;
  }

  public function getRefreshTokenExpiresAt(): ?string
  {
    return $this->refreshTokenExpiresAt;
  }

  public function getMfaMethod(): ?string
  {
    return $this->mfaMethod;
  }

  public function getMfaSecret(): ?string
  {
    return $this->mfaSecret;
  }

  // 🔹 Setters si nécessaire
  public function setRefreshToken(?string $token): void
  {
    $this->refreshToken = $token;
  }

  public function setRefreshTokenExpiresAt(?string $expiresAt): void
  {
    $this->refreshTokenExpiresAt = $expiresAt;
  }
}
