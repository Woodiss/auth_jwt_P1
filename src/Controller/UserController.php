<?php

namespace App\Controller;

use App\Security\Authenticated;
use App\Repository\ReservationRepository;

class UserController
{
  private $twig;

  public function __construct($twig)
  {
    $this->twig = $twig;
  }

  // #[Authenticated]
  public function profile()
  {
    $user = [
      "id" => 1,
      "firstname" => "Jhon",
      "lastname" => "Doe"
    ];

    $reservation = (new ReservationRepository())->findByUserId($user['id']);
    $user = [
      "id" => 1,
      "firstname" => "Jhon",
      "lastname" => "Doe",
      "reservation" => $reservation
    ];
    // Ici, on pourrait récupérer des infos supplémentaires de la DB
    echo $this->twig->render('profile.html.twig', [
      'user' => $user, // injecté via middleware si nécessaire
      'reservation' => $reservation,
    ]);
  }
}
