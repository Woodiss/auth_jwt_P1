<?php
namespace App\Repository;

use App\Database\Connexion;
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

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $frirstname, string $lastname, string $email, string $password, string $role): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `user`(`firstname`, `lastname`, `email`, `password`, `role`) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $frirstname,
            $lastname,
            $email,
            $password,
            $role
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
