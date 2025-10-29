<?php
namespace App\Repository;

use PDO;
use App\Database\Connexion;
use App\Entity\Spectacle;

final class SpectacleRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        // Récupère le PDO depuis Connexion (singleton), ou utilise celui injecté
        $this->pdo = $pdo ?? Connexion::get();
    }

    /**
     * CREATE — ajoute un spectacle et retourne son id.
     */
    public function create(Spectacle $spectacle): int
    {
        $sql = 'INSERT INTO spectacle (title, description, director) VALUES (?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $spectacle->getTitle(),
            $spectacle->getDescription(),
            $spectacle->getDirector()
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $spectacle->setId($id);
        return $id;
    }

    /**
     * READ — récupère un spectacle par id.
     */
    public function find(int $id): ?array
    {
        $sql = 'SELECT *
                FROM spectacle
                WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * READ (liste) — liste paginée, triée par date.
     */
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $limit  = max(0, (int)$limit);
        $offset = max(0, (int)$offset);

        $sql = 'SELECT * FROM spectacle';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * UPDATE — met à jour un spectacle. $fields peut contenir: titre, description, date_spectacle.
     * Retourne true si au moins une ligne a été modifiée.
     */
    public function update(int $id, array $fields): bool
    {
        // Whitelist des colonnes modifiables
        $allowed = ['titre', 'description', 'date_spectacle'];
        $set = [];
        $params = [];

        foreach ($fields as $col => $val) {
            if (in_array($col, $allowed, true)) {
                $set[] = "{$col} = ?";
                $params[] = $val;
            }
        }

        if (!$set) {
            return false; // Rien à mettre à jour
        }

        $params[] = $id;

        $sql = 'UPDATE spectacles SET ' . implode(', ', $set) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * DELETE — supprime un spectacle par id.
     * Retourne true si une ligne a été supprimée.
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM spectacles WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Utilitaire — nombre total de spectacles (utile pour pagination).
     */
    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM spectacles');
        return (int) $stmt->fetchColumn();
    }
}
