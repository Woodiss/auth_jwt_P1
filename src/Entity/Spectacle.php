<?php

namespace App\Entity;

class Spectacle
{
    private ?int $id = null;

    private ?string $title = null;
    
    private ?string $description = null;
    
    private ?string $director = null;

    public function getId(): ?int { 
        return $this->id; 
    }
    public function setId(?int $id): self { 
        $this->id = $id; 
        return $this; 
    }

    public function getTitle(): ?string { 
        return $this->title; 
    }
    public function setTitle(?string $title): self { 
        $this->title = $title; 
        return $this; 
    }

    public function getDescription(): ?string { 
        return $this->description; 
    }
    public function setDescription(?string $description): self { 
        $this->description = $description; 
        return $this; 
    }

    public function getDirector(): ?string { 
        return $this->director; 
    }
    public function setDirector(?string $director): self { 
        $this->director = $director; 
        return $this; 
    }
}
