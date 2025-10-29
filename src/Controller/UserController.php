<?php

namespace App\Controller;

use App\Security\Authenticated;

class UserController
{
  private $twig;

  public function __construct($twig)
  {
    $this->twig = $twig;
  }

  #[Authenticated]
  public function profile()
  {
    $user = [
      "id" => 1,
      "firstname" => "Jhon",
      "lastname" => "Doe",
      "reservations" => [
        1 => [
          "title" => "spectacle 1"
        ],
        2 => [
          "title" => "spectacle 2"
        ]
      ]
    ];
    // Ici, on pourrait récupérer des infos supplémentaires de la DB
    echo $this->twig->render('profile.html.twig', [
      'user' => $user, // injecté via middleware si nécessaire
    ]);
  }
}
