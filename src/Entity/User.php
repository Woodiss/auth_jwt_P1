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
  private ?string $twoFactorMethod = null;
  private ?string $twoFactorSecret = null;
  private ?string $phone = null;
  private ?string $refreshToken = null;
  private ?string $refreshTokenExpiresAt = null;

  public function __construct(
    string $firstName,
    string $lastName,
    string $email,
    string $passwordHash,
    ?int $id = null,
    ?string $role = null,
    ?string $twoFactorMethod = null,
    ?string $twoFactorSecret = null,
    ?string $phone = null,
    ?string $refreshToken = null,
    ?string $refreshTokenExpiresAt = null
  ) {
    $this->id = $id;
    $this->firstName = $firstName;
    $this->lastName  = $lastName;
    $this->email = $email;
    $this->passwordHash = $passwordHash;
    $this->role = $role ?? 'user';
    $this->twoFactorMethod = $twoFactorMethod;
    $this->twoFactorSecret = $twoFactorSecret;
    $this->phone = $phone;
    $this->refreshToken = $refreshToken;
    $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
  }

  // === Getters ===
  public function getId(): ?int
  {
    return $this->id;
  }
  public function getFirstname(): string
  {
    return $this->firstName;
  }
  public function getLastname(): string
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
  public function getTwoFactorMethod(): ?string
  {
    return $this->twoFactorMethod;
  }
  public function getTwoFactorSecret(): ?string
  {
    return $this->twoFactorSecret;
  }
  public function getPhone(): ?string
  {
    return $this->phone;
  }
  public function getRefreshToken(): ?string
  {
    return $this->refreshToken;
  }
  public function getRefreshTokenExpiresAt(): ?string
  {
    return $this->refreshTokenExpiresAt;
  }

  // === Setters ===
  public function setTwoFactorMethod(?string $method): void
  {
    $this->twoFactorMethod = $method;
  }
  public function setTwoFactorSecret(?string $secret): void
  {
    $this->twoFactorSecret = $secret;
  }
  public function setPhone(?string $phone): void
  {
    $this->phone = $phone;
  }
  public function setRefreshToken(?string $token): void
  {
    $this->refreshToken = $token;
  }
  public function setRefreshTokenExpiresAt(?string $expiresAt): void
  {
    $this->refreshTokenExpiresAt = $expiresAt;
  }

  public function getFullName(): string
  {
    return trim($this->firstName . ' ' . $this->lastName);
  }
}
