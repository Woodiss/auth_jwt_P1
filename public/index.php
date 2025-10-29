<?php
require __DIR__ . '/../vendor/autoload.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Controller\SpectacleController;
use App\Controller\UserController;
use App\Security\JWT;
use App\Security\AuthMiddleware;
use App\Security\Authenticated;
use App\Controller\AuthController;

// --- JWT & Middleware ---
$jwt = new JWT('ma_cle_secrete', 3600);
$authMiddleware = new AuthMiddleware($jwt);


// --- Initialiser Twig ---
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['cache' => false]);

// --- Base path ---
$docRoot = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
$scriptDir = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
$basePath = str_replace($docRoot, '', $scriptDir);
$twig->addGlobal('basePath', $basePath);

// --- Routes ---
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$route = strpos($requestUri, $basePath) === 0 ? substr($requestUri, strlen($basePath)) : $requestUri;
$route = empty($route) || $route === '/' ? '/home' : $route;

// --- Contrôleurs ---
$spectacleController = new SpectacleController($twig, $authMiddleware);
$userController = new UserController($twig, $authMiddleware);
$authController = new AuthController($twig, $jwt, $basePath);
$routes = [
  '/home' => [$spectacleController, 'home'],
  '/spectacles' => [$spectacleController, 'list'],
  '/profile' => [$userController, 'profile'], // ← route protégée
  '/login'       => [$authController, 'login'],
  '/register'    => [$authController, 'register'],
  '/logout'      => [$authController, 'logout'],
  '/spectacles/new' => [$spectacleController, 'new'],
  '/refresh' => fn() => print("Route de refresh token"),
];

// --- Gestion dynamique des spectacles individuels ---
if (preg_match('#^/spectacles/(\d+)$#', $route, $matches)) {
  $spectacleController->show((int)$matches[1]);
  exit;
}

// --- Routage principal ---
if (isset($routes[$route])) {
  $controller = $routes[$route][0];
  $method = $routes[$route][1] ?? null;

  if ($method) {
    // Vérifie s’il y a un attribut #[Authenticated]
    $refMethod = new ReflectionMethod($controller, $method);
    $attributes = $refMethod->getAttributes(\App\Security\Authenticated::class);

    if (!empty($attributes)) {
      $attribute = $attributes[0]->newInstance();
      $authMiddleware->requireAuth($attribute->roles);
    }

    $controller->$method();
  } else {
    $routes[$route](); // closure
  }
} else {
  http_response_code(404);
  echo $twig->render('error.html.twig', [
    'code' => 404,
    'message' => "Page non trouvée 😢"
  ]);
}
