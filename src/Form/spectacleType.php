<?php

declare(strict_types=1);

namespace App\Form;

class SpectacleType
{
    public static function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Titre',
                'type' => 'text',
                'required' => true,
                'maxlength' => 150,
                'placeholder' => 'Entrez le titre du spectacle',
            ],
            'description' => [
                'label' => 'Description',
                'type' => 'textarea',
                'required' => false,
                'maxlength' => 1000,
                'placeholder' => 'Brève description du spectacle (facultatif)',
            ],
            'director' => [
                'label' => 'Metteur en scène',
                'type' => 'text',
                'required' => true,
                'maxlength' => 100,
                'placeholder' => 'Nom du metteur en scène',
            ],
        ];
    }
}
