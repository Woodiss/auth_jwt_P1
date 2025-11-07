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

    $row = $stmt->fetch();
    if (!$row) {
      return null;
    }

    return new User(
      id: (int)$row['id'],
      firstName: $row['firstname'],
      lastName: $row['lastname'],
      email: $row['email'],
      passwordHash: $row['password'],
      role: $row['role']
    );
  }

  public function findByEmail(string $email): ?User
  {
    $stmt = $this->pdo->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) {
      return null;
    }

    return new User(
      id: (int)$row['id'],
      firstName: $row['firstname'],
      lastName: $row['lastname'],
      email: $row['email'],
      passwordHash: $row['password'],
      role: $row['role']
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
      refreshToken: $row['refresh_token'] ?? null,
      refreshTokenExpiresAt: $row['refresh_token_expires_at'] ?? null
    );
  }
  public function updateRefreshToken(int $userId, string $token, string $expiresAt): void
  {
    $stmt = $this->pdo->prepare('UPDATE user SET refresh_token = ?, refresh_token_expires_at = ? WHERE id = ?');
    $stmt->execute([$token, $expiresAt, $userId]);
  }

  public function disableMfaForUser($userId): void
  {
    $sql = "UPDATE user
            SET mfa_method = NULL, 
                mfa_totp = NULL, 
                mfa_email_otp = NULL 
            WHERE id = ?";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$userId]);
  }

  public function storeEmailOtpBundle(string $bundle, $userId): void
  {
      $sql = "UPDATE user
              SET mfa_method = 'EMAIL',
                  mfa_totp = NULL, 
                  mfa_email_otp = ? 
              WHERE id = ?";
      
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute([$bundle, $userId]);
  }

  public function storeTotpBlob(string $blob, $userId): void
  {
      $sql = "UPDATE user
              SET mfa_method = 'TOTP',
                  mfa_totp = ?,
                  mfa_email_otp = NULL
              WHERE id = ?";

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute([$blob, $userId]);
  }
}
