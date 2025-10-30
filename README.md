# Mini-application PHP de gestion de spectacles

Projet académique d’application web en **PHP orienté objet**, intégrant un système d’**authentification JWT sans dépendance externe (tel que Firebase)**.  
L’application simule un **site de réservation de spectacles** avec une gestion complète des utilisateurs, rôles et accès.  

L’objectif est d’illustrer les **fondamentaux d’une architecture MVC simplifiée** :  
Routeur, contrôleurs, entités, vues, et sécurité via **tokens JWT + refresh tokens**.

---

### 💡 Fonctionnalités principales

- 🔓 **Pages publiques** : accueil, liste et fiches des spectacles  
- 🔐 **Pages utilisateurs** : réservation de places, profil et historique des réservations  
- 🛠️ **Pages administrateurs** : ajout et gestion des spectacles  
- ⚙️ **Gestion des droits** via middleware `#[IsGranted]` et attributs PHP  
- 💾 **Base de données** : MySQL / phpMyAdmin  

---

## ⚙️ Installation & Prérequis

### Prérequis techniques
Avant d’installer le projet, assurez-vous d’avoir :

- Un **serveur local** (XAMPP, Laragon, WAMP, etc.)  
- **PHP 8** ou version ultérieure  
- **Composer** pour la gestion des dépendances  
- Une base de données **MySQL** (via phpMyAdmin)

---

## 📦 Étapes d’installation du projet

### 1. Cloner le dépôt
```bash
git clone https://github.com/Woodiss/auth_jwt_P1.git
cd auth_jwt_P1
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Configurer l’environnement
Copiez le fichier `.env.example` en `.env`, puis adaptez vos identifiants :

```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=auth_jwt_p1
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

### 4. Importer la base de données
1. Ouvrez **phpMyAdmin**  
2. Créez une base de données nommée `auth_jwt_p1`  
3. Importez le fichier SQL fourni à la racine du projet :
   ```
   auth_jwt_p1.sql
   ```

### 5. Lancer le serveur
Si vous utilisez un serveur intégré PHP :
```bash
php -S localhost:8000 -t public
```

Puis ouvrez [http://localhost](http://localhost) dans votre navigateur suivant de l'emplacement du projet.

---

### 6. Comptes déjà créer et utilisable
Comptes utilisateurs : 
  - Email: compte.user@gmail.fr
  - Mot de passe: azertyui

compte admin : 
  - Email: compte.admin@gmail.fr 
  - Mot de passe: azertyui

## 👥 Contributeurs

| Prénom| Nom | Rôle |
|------|------|------|
| Amaury |**Sanchez** | Développeur |
| Adrien |**Allard** | Développeur |
| Stéphane |**Descarpentries** | Développeur |
| Christopher| **De Pasqual** | Développeur |