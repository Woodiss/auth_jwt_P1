<?php

namespace App\Repository;

use App\Database\Connexion;
use App\Entity\User;
use PDO;

final class UserRepository
{
  private PDO $pdo;

  public function __construct()
  {
    // Récupère le PDO créé par Connexion (singleton)
    $this->pdo = Connexion::get();
  }


  public function find(int $id): ?User
  {
    $stmt = $this->pdo->prepare('SELECT * FROM user WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) return null;

    return new User(
      id: (int)$row['id'],
      firstName: $row['firstname'],
      lastName: $row['lastname'],
      email: $row['email'],
      passwordHash: $row['password'],
      role: $row['role'],
      twoFactorMethod: $row['two_factor_method'] ?? null,
      twoFactorSecret: $row['two_factor_secret'] ?? null,
      phone: $row['phone'] ?? null,
      refreshToken: $row['refresh_token'] ?? null,
      refreshTokenExpiresAt: $row['refresh_token_expires_at'] ?? null
    );
  }



  public function findByEmail(string $email): ?User
  {
    $stmt = $this->pdo->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row) return null;

    return new User(
      id: (int)$row['id'],
      firstName: $row['firstname'],
      lastName: $row['lastname'],
      email: $row['email'],
      passwordHash: $row['password'],
      role: $row['role'],
      twoFactorMethod: $row['two_factor_method'] ?? null,
      twoFactorSecret: $row['two_factor_secret'] ?? null,
      phone: $row['phone'] ?? null,
      refreshToken: $row['refresh_token'] ?? null,
      refreshTokenExpiresAt: $row['refresh_token_expires_at'] ?? null
    );
  }


  public function create(User $user): int
  {
    $stmt = $this->pdo->prepare(
      'INSERT INTO `user`(`firstname`, `lastname`, `email`, `password`) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
      $user->getFirstname(),
      $user->getLastname(),
      $user->getEmail(),
      $user->getPasswordHash(),
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  // refresh_token
  public function saveRefreshToken(int $userId, string $token, string $expiresAt): void
  {
    $stmt = $this->pdo->prepare('
        UPDATE user SET refresh_token = ?, refresh_token_expires_at = ? WHERE id = ?
    ');
    $stmt->execute([$token, $expiresAt, $userId]);
  }


  public function findByRefreshToken(string $token): ?User
  {
    $stmt = $this->pdo->prepare('
        SELECT * FROM user WHERE refresh_token = ? AND refresh_token_expires_at > NOW()
    ');
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) return null;

    return new User(
      id: (int)$row['id'],
      firstName: $row['firstname'],
      lastName: $row['lastname'],
      email: $row['email'],
      passwordHash: $row['password'],
      role: $row['role'],
      twoFactorMethod: $row['two_factor_method'] ?? null,
      twoFactorSecret: $row['two_factor_secret'] ?? null,
      phone: $row['phone'] ?? null,
      refreshToken: $row['refresh_token'] ?? null,
      refreshTokenExpiresAt: $row['refresh_token_expires_at'] ?? null
    );
  }


  public function updateRefreshToken(int $userId, string $token, string $expiresAt): void
  {
    $stmt = $this->pdo->prepare('UPDATE user SET refresh_token = ?, refresh_token_expires_at = ? WHERE id = ?');
    $stmt->execute([$token, $expiresAt, $userId]);
  }
  public function updateTwoFactorSecret(int $userId, string $secret, string $method): void
  {
    $stmt = $this->pdo->prepare('
        UPDATE user SET two_factor_secret = ?, two_factor_method = ? WHERE id = ?
    ');
    $stmt->execute([$secret, $method, $userId]);
  }

  public function disableTwoFactor(int $userId): bool
  {
    $stmt = $this->pdo->prepare('
        UPDATE user SET two_factor_secret = NULL, two_factor_method = "none" WHERE id = ?
    ');
    $stmt->execute([$userId]);

    // Renvoie true si au moins une ligne a été modifiée
    return $stmt->rowCount() > 0;
  }

  public function updatePhone(int $userId, string $phone): void
  {
    $sql = "UPDATE user SET phone = :phone WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':phone' => $phone,
      ':id' => $userId
    ]);
  }
}
