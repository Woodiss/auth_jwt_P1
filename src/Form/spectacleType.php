<?php

declare(strict_types=1);

namespace App\Form;

class SpectacleType
{
    public static function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Titre*',
                'placeholder' => 'Titre du spectacle',
                'type' => 'text',
                'required' => true,
            ],

            'description' => [
                'label' => 'Description',
                'placeholder' => 'Description du spectacle',
                'type' => 'textarea',
                'required' => false,
            ],
            
            'director' => [
                'label' => 'Metteur en scène*',
                'placeholder' => 'Nom du metteur en scène',
                'type' => 'text',
                'required' => true,
            ],
        ];
    }
}
