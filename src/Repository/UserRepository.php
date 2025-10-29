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
        $stmt = $this->pdo->prepare('SELECT id, email, name, password_hash, roles_json FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, name, password_hash, roles_json FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $email, string $passwordHash, string $name, array $roles = ['ROLE_USER']): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash, name, roles_json) 
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $email,
            $passwordHash,
            $name,
            json_encode($roles, JSON_UNESCAPED_UNICODE)
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
