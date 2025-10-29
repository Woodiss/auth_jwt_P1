
<?php
// 1. Charger Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Importer Twig et classes
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Controller\SpectacleController;
use App\Controller\UserController;
use App\Controller\AuthController;
use App\Security\JWT;
use App\Security\AuthMiddleware;

// --- JWT & Middleware ---
$jwt = new JWT('ma_cle_secrete', 3600); // clé secrète et durée token
$authMiddleware = new AuthMiddleware($jwt);
$authController = new AuthController($userRepository, $twig, $jwt);

// 3. Initialiser Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['cache' => false]);

// 4. Déterminer le chemin de base
$docRoot = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
$scriptDir = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
$basePath = str_replace($docRoot, '', $scriptDir);
$twig->addGlobal('basePath', $basePath);

// 5. Déterminer la route demandée
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$route = strpos($requestUri, $basePath) === 0 ? substr($requestUri, strlen($basePath)) : $requestUri;
$route = empty($route) || $route === '/' ? '/home' : $route;

// 6. Instancier les contrôleurs
$spectacleController = new SpectacleController($twig);
$userController = new UserController($twig);
// $authController = new AuthController($twig);

// 7. Routage
$routes = [
  '/home' => [$spectacleController, 'home'],
  '/spectacles' => [$spectacleController, 'list'],
  '/profile' => [$userController, 'profile'], // ← route protégée
  '/login'       => [$authController, 'login'],
  '/register'    => [$authController, 'register'],
  '/logout'      => [$authController, 'logout'],  
  '/refresh' => fn() => print("Route de refresh token"),
];

// 8. Exécuter la route
if (isset($routes[$route])) {
  $controller = $routes[$route][0];
  $method = $routes[$route][1] ?? null;

  if ($method) {
    // Vérifie si c'est la route profile et applique le middleware
    if ($route === '/profile') {
      $user = $authMiddleware->requireAuth(); // vérifie le JWT
      // Si nécessaire, tu peux passer $user au contrôleur
    }

    $controller->$method();
  } else {
    // pour les closures
    $routes[$route]();
  }
} else {
  http_response_code(404);
  echo $twig->render('404.html.twig');
}
