<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\JWT;
use Twig\Environment as Twig;
use App\Service\TwoFactorService;
use App\Security\AuthMiddleware;

final class AuthController
{
  private string $basePath;
  private AuthMiddleware $auth;

  public function __construct(
    private Twig $twig,   // rendu des vues
    private JWT $jwt,
    string $basePath,
    AuthMiddleware $auth
  ) {
    $this->basePath = $basePath;
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
      'twoFactorMethod ' => $userData['twoFactorMethod ']
    ];
  }
  /** GET/POST /login */



  public function login(): void
  {
    session_start(); // indispensable pour stocker l’utilisateur temporairement pour 2FA

    $data = [
      'email'    => $_POST['email'] ?? '',
      'password' => $_POST['password'] ?? '',
      'otp'      => $_POST['otp'] ?? '',
    ];

    $errors = [];
    $repo = new UserRepository();
    $twoFactorService = new TwoFactorService();

    // ----------------- PHASE OTP -----------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($data['otp'])) {

      if (!isset($_SESSION['2fa_user_id'])) {
        $errors['otp'] = 'Session expirée, reconnectez-vous.';
        echo $this->twig->render('login_2fa.html.twig', ['errors' => $errors]);
        return;
      }

      $user = $repo->find($_SESSION['2fa_user_id']);

      // Vérifie le code selon la méthode 2FA de l’utilisateur
      if ($twoFactorService->verifyCode($user->getTwoFactorMethod(), $user->getTwoFactorSecret(), $data['otp'])) {
        // ✅ Code correct → login final
        unset($_SESSION['2fa_user_id']); // nettoyage
        $this->finalizeLogin($user, $repo);
        return;
      } else {
        $errors['otp'] = 'Code OTP invalide.';
        echo $this->twig->render('login_2fa.html.twig', [
          'errors' => $errors,
          'email' => $user->getEmail()
        ]);
        return;
      }
    }

    // ----------------- PHASE MOT DE PASSE -----------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($data['email']) && !empty($data['password'])) {

      $user = $repo->findByEmail($data['email']);
      if (!$user || !password_verify($data['password'], $user->getPasswordHash())) {
        $errors['global'] = 'Email ou mot de passe incorrect.';
      } else {

        // Vérifie si l’utilisateur a activé la 2FA
        if ($user->getTwoFactorSecret()) {
          $_SESSION['2fa_user_id'] = $user->getId();
          $method = $user->getTwoFactorMethod();

          // Si la méthode est email ou sms, on génère et envoie un code temporaire
          if ($method === 'email' || $method === 'sms') {
            $code = $twoFactorService->generateSecret($method);
            $repo->updateTwoFactorSecret($user->getId(), $code, $method);

            if ($method === 'email') {
              $twoFactorService->sendEmailCode($user->getEmail(), $code);
            } elseif ($method === 'sms') {
              $twoFactorService->sendSmsCode($user->getPhone(), $code);
            }
          }

          // Affiche le formulaire OTP
          echo $this->twig->render('login_2fa.html.twig', [
            'errors' => $errors,
            'email' => $user->getEmail()
          ]);
          return;
        } else {
          // Pas de 2FA → login direct
          $this->finalizeLogin($user, $repo);
          return;
        }
      }
    }

    // ----------------- AFFICHAGE DU FORMULAIRE -----------------
    echo $this->twig->render('login.html.twig', [
      'data'    => $data,
      'errors'  => $errors,
      'success' => false,
    ]);
  }

  /**
   * Sépare la logique de génération des tokens
   */
  private function finalizeLogin(User $user, UserRepository $repo): void
  {
    $token = $this->jwt->generate([
      'id' => $user->getId(),
      'firstname' => $user->getFirstName(),
      'lastname' => $user->getLastName(),
      'email' => $user->getEmail(),
      'role' => $user->getRole(),
      'fullname' => $user->getFullname(),
      'phone' => $user->getPhone(),
      'twoFactorMethod ' => $user->getTwoFactorMethod()
    ]);

    $this->setJwtCookie($token);

    $refreshToken = bin2hex(random_bytes(64));
    $expiresAt = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
    $repo->saveRefreshToken($user->getId(), $refreshToken, $expiresAt);

    setcookie('jwt_Refresh_P1', $refreshToken, [
      'expires' => time() + 7 * 24 * 60 * 60,
      'path' => '/',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);

    header('Location: ' . $this->basePath . '/profile', true, 303);
    exit;
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


  public function enable2FA(): void
  {
    session_start();
    $user = $this->getUser();
    if (!$user) {
      header('Location: ' . $this->basePath . '/login', true, 303);
      exit;
    }

    $twoFactorService = new TwoFactorService();
    $userRepository = new UserRepository();

    // Si l'utilisateur soumet le code
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
      $inputCode = trim($_POST['otp']);
      $method = $_SESSION['pending_2fa_method'] ?? 'otp';
      $secret = $_SESSION['pending_2fa_secret'] ?? null;

      if (!$secret) {
        echo $this->twig->render('enable_2fa_error.html.twig', [
          'message' => 'Session expirée. Veuillez recommencer la configuration 2FA.',
          'user' => $user
        ]);
        return;
      }

      // Vérifie le code en fonction de la méthode
      if ($twoFactorService->verifyCode($method, $secret, $inputCode)) {
        $userRepository->updateTwoFactorSecret($user['id'], $secret, $method);
        unset($_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_method']);

        echo $this->twig->render('enable_2fa_success.html.twig', [
          'message' => 'La double authentification est activée avec succès !',
          'user' => $user
        ]);
        return;
      }

      // Si code invalide → on réaffiche selon la méthode
      $error = 'Code invalide. Veuillez réessayer.';

      if ($method === 'otp') {
        $qrCodeUrl = $twoFactorService->getQrCodeUrl($user['email'], $secret, 'MonApp');
        $qrImage = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qrCodeUrl) . "&size=200x200";
        echo $this->twig->render('enable_2fa_verify.html.twig', [
          'qrImage' => $qrImage,
          'secret' => $secret,
          'error' => $error,
          'user' => $user
        ]);
      } else {
        echo $this->twig->render('enable_2fa_verify_code.html.twig', [
          'method' => $method,
          'error' => $error,
          'user' => $user
        ]);
      }
      return;
    }

    // Si l'utilisateur choisit une méthode (formulaire initial)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method'])) {
      $method = $_POST['method'];
      $_SESSION['pending_2fa_method'] = $method;

      $secret = $twoFactorService->generateSecret($method);
      $_SESSION['pending_2fa_secret'] = $secret;

      if ($method === 'otp') {
        $qrCodeUrl = $twoFactorService->getQrCodeUrl($user['email'], $secret, 'MonApp');
        $qrImage = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qrCodeUrl) . "&size=200x200";
        echo $this->twig->render('enable_2fa_verify.html.twig', [
          'qrImage' => $qrImage,
          'secret' => $secret,
          'error' => null,
          'user' => $user
        ]);
      } elseif ($method === 'email') {
        $twoFactorService->sendEmailCode($user['email'], $secret);
        echo $this->twig->render('enable_2fa_verify_code.html.twig', [
          'method' => 'email',
          'error' => null,
          'user' => $user
        ]);
      } elseif ($method === 'sms') {
        // ⚠️ ton user doit avoir un champ "phone" en BDD
        $twoFactorService->sendSmsCode($user['phone'], $secret);
        echo $this->twig->render('enable_2fa_verify_code.html.twig', [
          'method' => 'sms',
          'error' => null,
          'user' => $user
        ]);
      }
      return;
    }

    // Première étape → choix du mode 2FA
    echo $this->twig->render('enable_2fa_choice.html.twig', [
      'user' => $user
    ]);
  }
}
