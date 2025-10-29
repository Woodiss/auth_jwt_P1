<?php

namespace App\Controller;

use Twig\Environment; // On aura besoin de Twig

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
}
