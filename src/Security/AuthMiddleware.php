<?php

namespace App\Security;

class AuthMiddleware
{
  private JWT $jwt;

  public function __construct(JWT $jwt)
  {
    $this->jwt = $jwt;
  }

  public function requireAuth(): ?array
  {
    if (!isset($_COOKIE['jwt'])) {
      http_response_code(401);
      exit('Non autorisé');
    }

    $user = $this->jwt->verify($_COOKIE['jwt']);
    if (!$user) {
      http_response_code(401);
      exit('Token invalide ou expiré');
    }

    return $user;
  }
}
