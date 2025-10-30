<?php

namespace App\Security;

class AuthMiddleware
{
  private JWT $jwt;

  public function __construct(JWT $jwt)
  {
    $this->jwt = $jwt;
  }

  public function requireAuth(array $requiredRoles = []): ?array
  {
    // 1️⃣ Récupère le token d'accès
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';
    $token = '';

    if (str_starts_with($authHeader, 'Bearer ')) {
      $token = substr($authHeader, 7);
    } else {
      $token = $_COOKIE['jwt_Auth_P1'] ?? '';
    }

    $payload = null;

    // 2️⃣ Vérifie le token d'accès
    if ($token) {
      $payload = $this->jwt->verify($token);
    }

    // 3️⃣ Si token absent ou expiré, tente un refresh automatique
    if (!$payload && isset($_COOKIE['jwt_Refresh_P1'])) {
      $refreshToken = $_COOKIE['jwt_Refresh_P1'];
      $repo = new \App\Repository\UserRepository();
      $user = $repo->findByRefreshToken($refreshToken);
      if ($user && $user->getRefreshTokenExpiresAt() !== null && $user->getRefreshTokenExpiresAt() > date('Y-m-d H:i:s')) {
        // Génère un nouveau access token
        $newAccessToken = $this->jwt->generate([
          'id'        => $user->getId(),
          'email'     => $user->getEmail(),
          'role'      => $user->getRole(),
          'firstname' => $user->getFirstName(),
          'lastname'  => $user->getLastName(),
          'fullname' => $user->getFullName()
        ]);

        // Remplace le cookie existant
        setcookie('jwt_Auth_P1', $newAccessToken, [
          'expires'  => time() + 900, // 15 min
          'path'     => '/',
          'httponly' => true,
          'samesite' => 'Lax',
        ]);

        // Vérifie immédiatement le nouveau token pour le payload
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
      }
    }

    // 4️⃣ Si toujours aucun token valide → 401
    if (!$payload) {
      http_response_code(401);
      exit('Token manquant ou invalide');
    }

    // 5️⃣ Vérifie les rôles si nécessaires
    if (!empty($requiredRoles)) {
      $userRole = $payload['role'] ?? 'user';
      if (!in_array($userRole, $requiredRoles)) {
        http_response_code(403);
        exit('Accès refusé : rôle insuffisant');
      }
    }

    // 6️⃣ Retourne le payload pour la requête courante
    return $payload;
  }
}
