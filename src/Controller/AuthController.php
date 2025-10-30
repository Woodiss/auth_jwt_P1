<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\JWT;
use Twig\Environment as Twig;

final class AuthController
{
  private string $basePath;

  public function __construct(
    private Twig $twig,   // rendu des vues
    private JWT $jwt,
    string $basePath
  ) {
    $this->basePath = $basePath;
  }

  /** GET/POST /login */
  public function login(): void
  {
    $data = [
      'email'    => $_POST['email'] ?? '',
      'password' => $_POST['password'] ?? '',
    ];

    $errors  = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = array_map('trim', $data);

      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email invalide.';
      }
      if ($data['password'] === '') {
        $errors['password'] = 'Le mot de passe est obligatoire.';
      }

      if (!$errors) {
        try {
          $repo = new UserRepository();
          $user = $repo->findByEmail($data['email']);

          if (!$user || !password_verify($data['password'], $user->getPasswordHash())) {
            $errors['global'] = 'Email ou mot de passe incorrect.';
          } else {
            // Générer le JWT et poser le cookie
            $token = $this->jwt->generate([
              'id'   => $user->getId(),
              'firstname' => $user->getFirstName(),
              'lastname' => $user->getLastName(),
              'email' => $user->getEmail(),
              'role'  => $user->getRole(),
              'fullname'  => $user->getFullname(),
            ]);

            $this->setJwtCookie($token);
            $refreshToken = bin2hex(random_bytes(64)); // Token aléatoire sécurisé
            $expiresAt = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60); // 7 jours

            $repo->saveRefreshToken($user->getId(), $refreshToken, $expiresAt);

            setcookie('jwt_Refresh_P1', $refreshToken, [
              'expires' => time() + 7 * 24 * 60 * 60,
              'path' => '/',
              'httponly' => true,
              'samesite' => 'Lax',
              // 'secure' => true, // à activer si HTTPS
            ]);
            // PRG : redirection vers le profil
            header('Location: ' . $this->basePath . '/profile', true, 303);
            exit;
          }
        } catch (\Throwable $e) {
          $errors['global'] = "Erreur lors de la connexion.";
          var_dump($e);
        }
      }
    }

    echo $this->twig->render('login.html.twig', [
      'data'    => $data,
      'errors'  => $errors,
      'success' => $success,
    ]);
  }

  /** GET/POST /register */
  public function register(): void
  {
    $data = [
      'firstname' => $_POST['firstname'] ?? '',
      'lastname'  => $_POST['lastname'] ?? '',
      'email'     => $_POST['email'] ?? '',
      'password'  => $_POST['password'] ?? '',
    ];

    $errors  = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = array_map('trim', $data);

      if ($data['firstname'] === '')    $errors['firstname'] = 'Le prénom est obligatoire.';
      if ($data['lastname'] === '')     $errors['lastname'] = 'Le nom est obligatoire.';
      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email invalide.';
      if (strlen($data['password']) < 8) $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères.';

      if (!$errors) {
        $user = new User(
          firstName: $data['firstname'],
          lastName: $data['lastname'],
          email: $data['email'],
          passwordHash: password_hash($data['password'], PASSWORD_DEFAULT)
        );

        try {
          (new UserRepository())->create($user);
          $success = true;
          // Réinitialiser le formulaire
          $data = ['firstname' => '', 'lastname' => '', 'email' => '', 'password' => ''];
        } catch (\Throwable $e) {
          $errors['global'] = "Erreur lors de l'enregistrement.";
        }
      }
    }

    echo $this->twig->render('register.html.twig', [
      'data'    => $data,
      'errors'  => $errors,
      'success' => $success,
    ]);
  }

  /** POST /logout */
  public function logout(): void
  {
    $this->unsetJwtCookie();
    setcookie('jwt_Refresh_P1', '', [
      'expires'  => time() - 3600,
      'path'     => '/',
      'secure'   => !empty($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
    header('Location: ' . $this->basePath . '/login', true, 303);
    exit;
  }

  private function setJwtCookie(string $token): void
  {
    setcookie('jwt_Auth_P1', $token, [
      'expires'  => 0,             // token porte sa propre exp ; cookie session
      'path'     => '/',
      'secure'   => !empty($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
  }

  private function unsetJwtCookie(): void
  {
    setcookie('jwt_Auth_P1', '', [
      'expires'  => time() - 3600,
      'path'     => '/',
      'secure'   => !empty($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
  }
  public function refresh(): void
  {
    $refreshToken = $_COOKIE['jwt_Refresh_P1'] ?? null;
    if (!$refreshToken) {
      http_response_code(401);
      echo json_encode(['error' => 'Missing refresh token']);
      return;
    }

    $repo = new UserRepository();
    $user = $repo->findByRefreshToken($refreshToken);
    if (!$user) {
      http_response_code(403);
      echo json_encode(['error' => 'Invalid or expired refresh token']);
      return;
    }

    // Nouveau access token JWT (15 min)
    $accessToken = $this->jwt->generate([
      'id' => $user->getId(),
      'email' => $user->getEmail(),
      'role' => $user->getRole(),
      'firstname' => $user->getFirstname(),
      'lastname' => $user->getLastname(),
    ], 900);

    setcookie('jwt_Auth_P1', $accessToken, [
      'expires' => time() + 900,
      'path' => '/',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);

    echo json_encode(['status' => 'Access token refreshed']);
  }
}
