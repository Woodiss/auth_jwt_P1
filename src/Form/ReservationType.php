<?php

namespace App\Form; 

class Reservation
{
    public static function getFields(): array
    {
        return [
            'reservation_date' => [
                'label' => 'Date de réservation*',
                'type' => 'date',
                'required' => true,
            ],
        ];
    }
}