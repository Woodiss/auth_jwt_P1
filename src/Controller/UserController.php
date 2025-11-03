<?php

namespace App\Controller;

use App\Security\Authenticated;
use App\Repository\ReservationRepository;
use App\Security\AuthMiddleware;

class UserController
{
  private $twig;
  private AuthMiddleware $auth;

  public function __construct($twig, AuthMiddleware $auth)
  {
    $this->twig = $twig;
    $this->auth = $auth;
  }
  private function getUser(): ?array
  {
    $payload = $_COOKIE['jwt_Auth_P1'] ?? null;
    if (!$payload) return null;
    $userData = $this->auth->requireAuth([]);
    if (!$userData) return null;
    return [
      'id'        => $userData['id'],
      'firstname' => $userData['firstname'],
      'lastname'  => $userData['lastname'],
      'email'     => $userData['email'],
      'role'      => $userData['role'],
      'fullname'  => $userData['fullname'],
      'phone' => $userData['phone'],
      'twoFactorMethod' => $userData['twoFactorMethod ']
    ];
  }

  #[Authenticated]
  public function profile()
  {
    $user = $this->getUser();

    $reservation = (new ReservationRepository())->findByUserId($user['id']);
    $user = [
      "reservation" => $reservation,
      'twoFactorMethod' => $user['twoFactorMethod']

    ];
    print_r($_ENV);
    // Ici, on pourrait récupérer des infos supplémentaires de la DB
    echo $this->twig->render('profile.html.twig', [
      'user' => $user, // injecté via middleware si nécessaire
      'reservation' => $reservation,
    ]);
  }
}
