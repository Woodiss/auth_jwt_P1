<?php

namespace App\Entity;

class Reservation

{
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $spectacleId = null;
    private ?\DateTime $date = null;

    public function __construct(?int $id = null, ?int $userId = null, ?int $spectacleId = null, ?\DateTime $date = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->spectacleId = $spectacleId;
        $this->date = $date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSpectacleId(): ?int
    {
        return $this->spectacleId;
    }

    public function setSpectacleId(?int $spectacleId): self
    {
        $this->spectacleId = $spectacleId;
        return $this;
    }
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }
}