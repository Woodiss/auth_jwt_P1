<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\JWT;
use Twig\Environment as Twig;

final class AuthController
{
    public function __construct(
        private readonly UserRepository $users, // accès aux users (PDO)
        private readonly Twig $twig,            // rendu des vues
        private readonly JWT $jwt,              // ton service JWT maison
    ) {}

    /** GET/POST /login */
    public function login(): string
    {
        // Si déjà connecté (cookie JWT présent et valide), redirige vers /profile
        if (!empty($_COOKIE['jwt']) && $this->jwt->verify($_COOKIE['jwt'])) {
            header('Location: /profile', true, 303);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Affichage simple, sans CSRF/flash
            return $this->twig->render('login.html.twig', [
                'errors' => [],
                'old'    => [],
            ]);
        }

        // --- POST: traite la soumission
        $emailRaw = trim((string)($_POST['email'] ?? ''));
        $email    = filter_var(mb_strtolower($emailRaw), FILTER_VALIDATE_EMAIL) ?: null;
        $password = (string)($_POST['password'] ?? '');

        $errors = [];
        if (!$email || $password === '') {
            $errors[] = 'Email ou mot de passe invalide.';
            return $this->twig->render('login.html.twig', [
                'errors' => $errors,
                'old'    => ['email' => $emailRaw],
            ]);
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user->getPasswordHash())) {
            // Message générique (ne révèle pas si l'email existe)
            return $this->twig->render('login.html.twig', [
                'errors' => ['Email ou mot de passe incorrect.'],
                'old'    => ['email' => $emailRaw],
            ]);
        }

        // Upgrade de hash si nécessaire (pro)
        if (password_needs_rehash($user->getPasswordHash(), PASSWORD_DEFAULT)) {
            $this->users->upgradePassword($user, password_hash($password, PASSWORD_DEFAULT));
        }

        // Génère un JWT (ton service attend déjà un exp global dans le constructeur)
        // Tu peux y mettre sub (id), email, role, name…
        $token = $this->jwt->generate([
            'sub'   => $user->getId(),
            'email' => $user->getEmail(),
            'role'  => $user->getRole(),
            'name'  => $user->getFullname(),
        ]);

        // Pose le cookie JWT (HttpOnly/Secure/SameSite=Lax)
        $this->setJwtCookie($token);

        // PRG vers /profile
        header('Location: /profile', true, 303);
        exit;
    }

    /** GET/POST /register (si tu souhaites l’exposer) */
    public function register(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->twig->render('register.html.twig', [
                'errors' => [],
                'old'    => [],
            ]);
        }

        $firstname = trim((string)($_POST['firstname'] ?? ''));
        $lastname  = trim((string)($_POST['lastname']  ?? ''));
        $emailRaw  = trim((string)($_POST['email']     ?? ''));
        $email     = filter_var(mb_strtolower($emailRaw), FILTER_VALIDATE_EMAIL) ?: null;
        $password  = (string)($_POST['password'] ?? '');

        $errors = [];
        if ($firstname === '' || $lastname === '') {
            $errors[] = 'Prénom et nom sont obligatoires.';
        }
        if (!$email) {
            $errors[] = "L'adresse email n'est pas valide.";
        }
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($email && $this->users->existsByEmail($email)) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }

        if ($errors) {
            return $this->twig->render('register.html.twig', [
                'errors' => $errors,
                'old'    => [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $emailRaw,
                ],
            ]);
        }

        // Crée l'user (ton entité est déjà pro)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(
            id: null,
            firstname: $firstname,
            lastname: $lastname,
            email: (string)$email,
            passwordHash: $hash,
        );

        try {
            $this->users->create($user);
            // PRG vers /login
            header('Location: /login', true, 303);
            exit;
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage() === 'EMAIL_TAKEN'
                ? 'Cette adresse email est déjà utilisée.'
                : 'Une erreur est survenue. Merci de réessayer.';
            error_log('[register] '.$e->getMessage());

            return $this->twig->render('register.html.twig', [
                'errors' => [$msg],
                'old'    => [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $emailRaw,
                ],
            ]);
        }
    }

    /** POST /logout (recommandé) */
    public function logout(): void
    {
        $this->unsetJwtCookie();
        header('Location: /login', true, 303);
        exit;
    }

    private function setJwtCookie(string $token): void
    {
        setcookie('jwt', $token, [
            'expires'  => 0,             // token porte sa propre exp ; cookie session
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function unsetJwtCookie(): void
    {
        setcookie('jwt', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
