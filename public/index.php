<?php
require __DIR__ . '/../vendor/autoload.php';

// Très simple routeur basé sur l'URL
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Enlever les query strings
$route = strtok($requestUri, '?');

// On instancie les contrôleurs
/* $authController = new App\Controller\AuthController(); */
/* $spectacleController = new App\Controller\SpectacleController(); */

switch ($route) {
  case '/home':
    /* $spectacleController->home(); */
    break;
  case '/spectacles':
    /* $spectacleController->list(); */
    break;
  case '/login':
    if ($requestMethod === 'POST') {
      /* $authController->login(); */
    }
    break;
  case '/refresh':
    if ($requestMethod === 'POST') {
      /* $authController->refresh(); */
    }
    break;
  case '/reserver':
    // C'est ici qu'on appellera la sécurité !
    /* $spectacleController->reserve(); */
    break;
  // etc.
  default:
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
