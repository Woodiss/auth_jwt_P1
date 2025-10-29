<?php

namespace App\Controller;

use App\Entity\Spectacle;
use Twig\Environment; // On aura besoin de Twig
use App\Form\SpectacleType;


class SpectacleController
{
  // On "injecte" Twig pour que le contrôleur puisse l'utiliser
  private Environment $twig;

  public function __construct(Environment $twig)
  {
    $this->twig = $twig;
  }

  /**
   * Méthode pour la page d'accueil
   */
  public function home(): void
  {
    // ... (logique pour savoir si l'utilisateur est connecté, etc.)

    $user = [
      "id" => 1,
      "firstname" => "Jhon",
      "lastname" => "Doe"
    ]; // À remplacer par le vrai nom si connecté

    // Le contrôleur fait son travail : il rend un template
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
    $spectacles = [
      ['id' => 1, 'nom' => 'Le Roi Lion'],
      ['id' => 2, 'nom' => 'Mamma Mia!']
    ];

    echo $this->twig->render('spectacles/list.html.twig', [
      'spectacles' => $spectacles
    ]);
  }

  public function new(): void
    {
        $fields = SpectacleType::getFields();

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'director' => $_POST['director'] ?? '',
        ];

        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (trim($data['title']) === '') {
                $errors['title'] = 'Le titre est obligatoire.';
            }
            if (trim($data['director']) === '') {
                $errors['director'] = 'Le metteur en scène est obligatoire.';
            }

            if (empty($errors)) {
                // Simulation d'enregistrement OK
                $success = true;
                $data = ['title' => '', 'description' => '', 'director' => ''];
            }
        }

        echo $this->twig->render('spectacles/new.html.twig', [
            'fields' => $fields,
            'data' => $data,
            'errors' => $errors,
            'success' => $success,
        ]);
    }

}
