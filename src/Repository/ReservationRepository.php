<?php

namespace App\Repository;
use PDO;
use App\Database\Connexion;
use App\Entity\Reservation;

class ReservationRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connexion::get();
    }

    public function create(Reservation $reservation): int
    {
        $sql = 'INSERT INTO reservation (user, spectacle, date) VALUES (?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $reservation->getUserId(),
            $reservation->getSpectacleId(),
            $reservation->getDate()?->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $reservation->setId($id);
        return $id;
    }

    public function findByUserId(int $userId): array
    {
        $sql = '
            SELECT r.id, r.date, s.title, s.description
            FROM reservations r
            JOIN spectacles s ON r.spectacle_id = s.id
            WHERE r.user_id = :user_id
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}