<?php

namespace App\Security;

class AuthMiddleware
{
  private JWT $jwt;

  public function __construct(JWT $jwt)
  {
    $this->jwt = $jwt;
  }

  public function requireAuth(array $requiredRoles = []): array
  {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';

    if (!str_starts_with($authHeader, 'Bearer ')) {
      http_response_code(401);
      exit('Token manquant');
    }

    $token = substr($authHeader, 7);
    $payload = $this->jwt->verify($token);

    if (!$payload) {
      http_response_code(401);
      exit('Token invalide ou expiré');
    }

    // Vérifie les rôles si nécessaires
    if (!empty($requiredRoles)) {
      $userRole = $payload['role'] ?? 'user';
      if (!in_array($userRole, $requiredRoles)) {
        http_response_code(403);
        exit('Accès refusé : rôle insuffisant');
      }
    }

    return $payload;
  }
}
