🎭 Mini-application PHP de gestion de spectacles

Projet académique d’application web en PHP orienté objet, intégrant un système complet d’authentification sécurisée via JWT, sans dépendance externe (comme Firebase).
L’application simule un site de réservation de spectacles avec gestion des utilisateurs, rôles, réservations et sécurité avancée.

Ce projet illustre les fondamentaux d’une architecture MVC moderne et simplifiée :
Routeur, contrôleurs, entités, vues, sécurité via JWT + Refresh Tokens, et désormais une authentification à deux facteurs (2FA) configurable.

✨ Fonctionnalités principales
🔓 Pages publiques

Accueil, liste et fiche des spectacles

🔐 Pages utilisateurs

Réservation de places

Profil et historique des réservations

Activation et gestion de la double authentification (2FA) :

🔢 Application d’authentification (OTP) (Google Authenticator, Authy, etc.)

✉️ Code de vérification par e-mail

📱 Code de vérification par SMS (via Twilio)

🛠️ Pages administrateurs

Ajout, modification et suppression des spectacles

⚙️ Sécurité et architecture

Gestion des droits via middleware #[IsGranted] et #[Authenticated]

Authentification JWT + Refresh Tokens (sans librairie externe)

Mise à jour automatique du token lors des changements de méthode 2FA

Séparation stricte MVC : routeur, contrôleurs, vues, entités, repositories

💾 Base de données

MySQL / phpMyAdmin

Tables : user, spectacle, reservation, etc.

⚙️ Installation & Prérequis
🧰 Prérequis techniques

Avant d’installer le projet, assurez-vous d’avoir :

Un serveur local PHP (MAMP, XAMPP, WAMP, Laragon, etc.)

PHP 8.0+

Composer

Une base MySQL

Un compte Twilio (pour l’envoi de SMS)

Une clé SendGrid (pour l’envoi d’e-mails)

📦 Étapes d’installation

1. Cloner le dépôt

