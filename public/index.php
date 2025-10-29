
<?php
// 1. Charger Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Importer Twig
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// 3. Importer les contrôleurs
use App\Controller\SpectacleController;
use App\Controller\AuthController; // pour plus tard

// 4. Initialiser Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['cache' => false]);

// 5. Déterminer le chemin de base (utile si le projet est dans un sous-dossier)
$docRoot = str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['DOCUMENT_ROOT']);
$scriptDir = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
$basePath = str_replace($docRoot, '', $scriptDir);

// Ajouter la variable globale Twig
$twig->addGlobal('basePath', $basePath);

// 6. Déterminer la route demandée
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // enlever les paramètres GET

// Retirer le chemin de base de l’URI (si présent)
if (strpos($requestUri, $basePath) === 0) {
  $route = substr($requestUri, strlen($basePath));
} else {
  $route = $requestUri;
}

// Si la route est vide, rediriger vers /home
if (empty($route) || $route === '/') {
  $route = '/home';
}

// 7. Instancier les contrôleurs
$spectacleController = new SpectacleController($twig);
// $authController = new AuthController($twig);

// 8. Routage simple
$routes = [
  '/home' => fn() => $spectacleController->home(),
  '/spectacles' => fn() => $spectacleController->list(),
  '/spectacles/new' => fn() => $spectacleController->new(),
  '/login' => fn() => print("Page de login"),
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
