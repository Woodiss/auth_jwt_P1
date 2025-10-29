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

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM user WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
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
            id:          (int)$row['id'],
            firstName:   $row['firstname'],
            lastName:    $row['lastname'],
            email:       $row['email'],
            passwordHash:$row['password'],
            role:        $row['role']
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
}
