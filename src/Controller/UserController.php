<?php

namespace App\Controller;

use App\Security\Authenticated;

class UserController
{
  private $twig;
  private \App\Security\AuthMiddleware $auth;

  public function __construct($twig, \App\Security\AuthMiddleware $auth)
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
      'fullname'  => $userData['name'],
    ];
  }

  #[Authenticated]
  public function profile()
  {
    $user = $this->getUser();
    // Ici, on pourrait récupérer des infos supplémentaires de la DB
    echo $this->twig->render('profile.html.twig', [
      'user' => $user, // injecté via middleware si nécessaire
    ]);
  }
}
