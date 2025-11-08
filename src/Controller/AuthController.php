<?php

declare(strict_types=1);

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Security\JWT;
use Twig\Environment as Twig;
use App\Repository\UserRepository;
use App\Security\AuthMiddleware;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;


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
    ];
  }

  /** GET/POST /login */
  public function login(): void
  {
      session_start();

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
                      // ✅ Vérification de la double authentification
                      if ($user->getMfaMethod()) {

                          // === CAS OTP MAIL ===
                          if ($user->getMfaMethod() === 'EMAIL') {

                              // Génération du code à usage unique
                              $code    = (string) random_int(100000, 999999);
                              $hash    = password_hash($code, PASSWORD_DEFAULT);
                              $expires = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');
                              $bundle  = $hash . "|" . $expires;

                              // Sauvegarde du code haché et expiration
                              $repo->storeEmailOtpBundle($bundle, $user->getId());

                              // Envoi du mail
                              $mail = new PHPMailer(true);
                              try {
                                  $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
                                  $dotenv->load();
                                  $mail->isSMTP();
                                  $mail->Host       = $_ENV['SMTP_HOST'];
                                  $mail->SMTPAuth   = true;
                                  $mail->Username   = $_ENV['SMTP_USER'];
                                  $mail->Password   = $_ENV['SMTP_PASS'];
                                  $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
                                  $mail->Port       = (int) $_ENV['SMTP_PORT'];

                                  $mail->setFrom('noreply@authjwt.local', 'Auth_JWT_P1');
                                  $mail->addAddress($user->getEmail());
                                  $mail->isHTML(true);
                                  $mail->Subject = 'Votre code de vérification';
                                  $mail->Body    = "<p>Bonjour,</p>
                                                    <p>Votre code est : <b>$code</b></p>
                                                    <p>Il expire dans 5 minutes.</p>";

                                  $mail->send();
                              } catch (Exception $e) {
                                  $errors['global'] = "Impossible d'envoyer le mail : {$mail->ErrorInfo}";
                              }
                          }

                          // Stocke l'ID de l'utilisateur dans la session
                          $_SESSION['pending_user_id'] = $user->getId();

                          // Redirection vers la vérification MFA
                          header('Location: ' . $this->basePath . '/mfa/verify', true, 303);
                          exit;
                      }

                      // 🔓 Si pas de 2FA : connexion normale
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

  /** GET/POST /security */
  public function security(): void
  {
      $data = [
          'qrcode' => isset($_POST['qrcode']),
          'email'  => isset($_POST['email']),
      ];

      $errors  = [];
      $success = false;

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {

          // les deux cochées -> erreur
          if ($data['qrcode'] && $data['email']) {
              $errors['global'] = 'Merci de choisir une seule méthode (QR ou Email).';
          }

          // aucune cochée -> désactivation MFA
          if (!$data['qrcode'] && !$data['email'] && !$errors) {
              $repo = new UserRepository();
              $userId = $this->getUser()['id'];
              $repo->disableMfaForUser($userId); // 👉 À créer dans UserRepository
              $success = true;
          }

          // QR -> redirection TOTP setup
          if ($data['qrcode'] && !$data['email'] && !$errors) {
              header('Location: ' . $this->basePath . '/mfa/totp/begin', true, 303);
              exit;
          }

          // Email -> génération + redirection confirm
          if ($data['email'] && !$data['qrcode'] && !$errors) {
              // petite génération ici
              $code    = (string) random_int(100000, 999999);
              $hash    = password_hash($code, PASSWORD_DEFAULT);
              $expires = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');
              $bundle  = $hash . "|" . $expires;

              $repo = new UserRepository();
              $userId = $this->getUser()['id'];
              $repo->storeEmailOtpBundle($bundle, $userId);
              header('Location: ' . $this->basePath . '/', true, 303);
              exit;
          }
      }

      echo $this->twig->render('totp/user_secu.html.twig', [
          'user'    => $this->getUser(),
          'data'    => $data,
          'errors'  => $errors,
          'success' => $success,
      ]);
  }

  public function totpBegin(): void
  {
      session_start();
      // 2) Récupérer l'email (label du compte dans l'app 2FA)
      $repo = new UserRepository();
      $userId = $this->getUser()['id'];
      $user = $repo->find($userId);
      $label = $user ? $user->getEmail() : ('user' . $userId);

      // 3) Générer un secret TOTP temporaire + QR
      $tfa = new TwoFactorAuth(
          issuer: 'Auth_JWT_P1',                 // nom affiché dans l’app d’auth
          qrcodeprovider: new QRServerProvider() // <-- bon nom d’argument + provider existant
      );

      $secret   = $tfa->createSecret();
      $qrDataUri = $tfa->getQRCodeImageAsDataUri("user{$userId}", $secret);

      // Stocker le secret en session (temporaire, pour la confirmation)
      $_SESSION['pending_totp_secret_' . $userId] = $secret;

      // Data-URI du QR à afficher
      $qrDataUri = $tfa->getQRCodeImageAsDataUri($label, $secret);

      // 4) Afficher la page avec le QR + formulaire de code
      echo $this->twig->render('totp/mfa_totp_begin.html.twig', [
          'qr'       => $qrDataUri,
          'basePath' => $this->basePath,
          'user'     => $this->getUser()
      ]);
  }

  public function totpConfirm(): void
  {
    session_start();

    $user = $this->getUser();
    if (!$user) {
        header('Location: ' . $this->basePath . '/login', true, 303);
        exit;
    }

    $userId = $user['id'];
    $repo = new UserRepository();

    // Vérifie qu'un secret temporaire existe
    $secretKey = $_SESSION['pending_totp_secret_' . $userId] ?? null;
    if (!$secretKey) {
        http_response_code(400);
        echo "Aucun secret TOTP en attente. Recommencez l’enrôlement.";
        return;
    }

    $code = trim($_POST['code'] ?? '');
    $errors = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!preg_match('/^\d{6}$/', $code)) {
            $errors['code'] = 'Code invalide.';
        } else {
            $tfa = new TwoFactorAuth(
                issuer: 'Auth_JWT_P1',
                qrcodeprovider: new QRServerProvider()
            );

            // Vérifie le code TOTP
            if ($tfa->verifyCode($secretKey, $code)) {
                // Chiffre ou stocke directement le secret dans la BDD
                $repo->storeTotpBlob($secretKey, $userId);

                // Nettoie la session temporaire
                unset($_SESSION['pending_totp_secret_' . $userId]);

                $success = true;
                header('Location: ' . $this->basePath . '/profile');
                exit;
            } else {
                $errors['code'] = 'Code incorrect. Veuillez réessayer.';
            }
        }
    }

    echo $this->twig->render('totp/mfa_totp_confirm.html.twig', [
        'errors' => $errors,
        'success' => $success,
        'basePath' => $this->basePath,
    ]);
  }

  public function mfaVerify(): void
  {
      session_start();

      $repo = new UserRepository();
      $userId = $_SESSION['pending_user_id'] ?? null;

      if (!$userId) {
          // Pas d'utilisateur en attente : retour login
          header('Location: ' . $this->basePath . '/login', true, 303);
          exit;
      }

      $user = $repo->find($userId);
      if (!$user) {
          echo "Utilisateur introuvable.";
          return;
      }

      $method = $user->getMfaMethod();
      $secret = $user->getMfaSecret();
      $errors = [];

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $code = trim($_POST['code'] ?? '');

          if ($code === '' || !preg_match('/^\d{6}$/', $code)) {
              $errors['code'] = 'Code invalide.';
          } else {
              // Selon le type de MFA
              if ($method === 'TOTP') {
                  $tfa = new \RobThree\Auth\TwoFactorAuth(
                      new \RobThree\Auth\Providers\Qr\QRServerProvider(),
                      'Auth_JWT_P1'
                  );

                  if ($tfa->verifyCode($secret, $code)) {
                      unset($_SESSION['pending_user_id']);
                      $this->finalizeLogin($user);
                      return;
                  } else {
                      $errors['code'] = 'Code TOTP incorrect.';
                  }
              }

              if ($method === 'EMAIL') {
                  [$hash, $expires] = explode('|', $secret);
                  if (new \DateTime() > new \DateTime($expires)) {
                      $errors['code'] = 'Code expiré. Veuillez en redemander un.';
                  } elseif (!password_verify($code, $hash)) {
                      $errors['code'] = 'Code incorrect.';
                  } else {
                      unset($_SESSION['pending_user_id']);
                      $this->finalizeLogin($user);
                      return;
                  }
              }
          }
      }

      echo $this->twig->render('totp/mfa_verify.html.twig', [
          'errors'   => $errors,
          'method'   => $method,
          'basePath' => $this->basePath,
      ]);
  }

  private function finalizeLogin(User $user): void
  {
      $repo = new UserRepository();

      // Générer le JWT
      $token = $this->jwt->generate([
          'id'        => $user->getId(),
          'firstname' => $user->getFirstName(),
          'lastname'  => $user->getLastName(),
          'email'     => $user->getEmail(),
          'role'      => $user->getRole(),
          'fullname'  => $user->getFullname(),
      ]);

      $this->setJwtCookie($token);

      // Générer un refresh token
      $refreshToken = bin2hex(random_bytes(64));
      $expiresAt = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
      $repo->saveRefreshToken($user->getId(), $refreshToken, $expiresAt);

      setcookie('jwt_Refresh_P1', $refreshToken, [
          'expires' => time() + 7 * 24 * 60 * 60,
          'path' => '/',
          'httponly' => true,
          'samesite' => 'Lax',
          // 'secure' => true, // si HTTPS
      ]);

      // Redirige vers le profil
      header('Location: ' . $this->basePath . '/profile', true, 303);
      exit;
  }



}
