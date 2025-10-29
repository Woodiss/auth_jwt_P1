<?php

namespace App\Controller;

use App\Entity\Spectacle;
use Twig\Environment; // On aura besoin de Twig
use App\Form\SpectacleType;
use App\Repository\SpectacleRepository;

use App\Security\Authenticated;
use App\Security\AuthMiddleware;

class SpectacleController
{
  // On "injecte" Twig pour que le contrôleur puisse l'utiliser
  private Environment $twig;

  private AuthMiddleware $auth;

  public function __construct(\Twig\Environment $twig, AuthMiddleware $auth)
  {
    $this->twig = $twig;
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
  /**
   * Méthode pour la page d'accueil
   */

  public function home(): void
  {
    $user = $this->getUser();

    echo $this->twig->render('index.html.twig', [
      'user' => $user
    ]);
  }

  /**
   * Méthode pour la page "liste des spectacles"
   */
  public function list(): void
  {
    // Données "mock" (en attendant une BDD)

    $repoSpectacle = new SpectacleRepository();
    $spectacles = $repoSpectacle->findAll();

    $user = $this->getUser();
    echo $this->twig->render('spectacles/list.html.twig', [
      'spectacles' => $spectacles,
      'user' => $user
    ]);
  }
  public function show(int $id)
  {
    $user = $this->getUser();
    /* $spectacle = $this->getSpectacleById($id); */
    $repoSpectacle = new SpectacleRepository();
    $spectacle = $repoSpectacle->find($id);

    if (!$spectacle) {
      http_response_code(404);
      echo $this->twig->render('error.html.twig', [
        'code' => 404,
        'message' => "Page non trouvée 😢"
      ]);
      return;
    }

    echo $this->twig->render('spectacles/show.html.twig', [
      'spectacle' => $spectacle,
      'user' => $user
    ]);
  }
  #[Authenticated(roles: ['admin'])]
  public function new(): void
  {
    $user = $this->getUser();
    $fields = SpectacleType::getFields();

    $data = [
      'title' => $_POST['title'] ?? '',
      'description' => $_POST['description'] ?? '',
      'director' => $_POST['director'] ?? '',
    ];

    $errors = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = array_map('trim', $data);
      if ($data['title'] === '')    $errors['title'] = 'Le titre est obligatoire.';
      if ($data['director'] === '') $errors['director'] = 'Le metteur en scène est obligatoire.';

      if (!$errors) {
        $spectacle = new Spectacle(
          title: $data['title'],
          description: $data['description'] ?: null,
          director: $data['director']
        );

        try {
          (new SpectacleRepository())->create($spectacle);
          // PRG (recommandé) :
          // header('Location: /spectacles/new?success=1'); exit;
          $success = true;
          $data = ['title' => '', 'description' => '', 'director' => ''];
        } catch (\Throwable $e) {
          $errors['global'] = "Erreur lors de l'enregistrement.";
        }
      }
    }

    echo $this->twig->render('spectacles/new.html.twig', [
      'fields'  => $fields,
      'data'    => $data,
      'errors'  => $errors,
      'success' => $success,
      'user' => $user
    ]);
  }
}
