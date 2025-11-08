-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 08 nov. 2025 à 01:26
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `auth_jwt_p1`
--

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` int NOT NULL,
  `spectacle` int NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `spectacle` (`spectacle`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id`, `user`, `spectacle`, `date`) VALUES
(1, 5, 4, '2025-11-04'),
(2, 5, 1, '2025-11-03'),
(3, 5, 2, '2025-11-06'),
(4, 6, 2, '2025-10-31'),
(5, 6, 2, '2026-04-09'),
(6, 6, 2, '2027-11-24'),
(7, 6, 3, '2062-11-06'),
(8, 6, 1, '2025-11-04');

-- --------------------------------------------------------

--
-- Structure de la table `spectacle`
--

DROP TABLE IF EXISTS `spectacle`;
CREATE TABLE IF NOT EXISTS `spectacle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `director` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `spectacle`
--

INSERT INTO `spectacle` (`id`, `title`, `description`, `director`) VALUES
(1, 'Titanic', 'gros glaçon, gros bateau\r\nGlaçon 1 - 0 Bateau', 'Michael Jackson'),
(2, 'Dracula', 'Un aristocrate anémique fan de transfusions sauvages fait du Airbnb non consenti chez des Anglais en mode “je dors le jour et je mange les voisins la nuit”, le tout avec un dress code cape + slick back cheveux mouillés.', 'Dracula'),
(3, 'Harry Potter', 'Un bébé chauve marqué au front devient apprenti sorcier dans un pensionnat gothique rempli de chandelles low-cost et de profs traumatisés. Il passe son temps à jouer au quidditch (le foot mais en balai Ikea), à crier “Wingardium Leviosa” et à survivre au même chauve rancunier qui revient chaque année comme un virus sans antivirus.', 'Hagrid'),
(4, 'Indiana Jones', 'Un prof d\'archéologie qui déteste les serpents passe son temps à fuir des rochers géants et à fouetter des nazis pour leur piquer des vieux cailloux magiques.', 'DJ Snake');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `refresh_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `refresh_token_expires_at` datetime DEFAULT NULL,
  `mfa_method` enum('TOTP','EMAIL') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mfa_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `password`, `role`, `refresh_token`, `refresh_token_expires_at`, `mfa_method`, `mfa_secret`) VALUES
(1, 'STÉPHANE', 'DESCARPENTRIES', 'stephane.descarpentries@hotmail.fr', '$2y$10$fBhWaLbAewKciFCVjpTwNuF/IGmB8VP2Vqdj1A6D1jD7NCbmHaA4e', 'admin', 'c907a62c4a92d9f999fdfc95c56ed0f32010bcc8d7b507ad17d93a7e51453bf2286c9373604a8569b409a04acbaab0a7f16ac5a35ba9c20bff1848a8a81b1e3f', '2025-11-15 01:15:48', 'EMAIL', '$2y$10$PMXwojU4KbxsX1sILAYaXedgFlaFAaIsUKV4aWewjxM10AsKgN7gi|2025-11-08 01:20:35'),
(2, 'Amaury', 'Sanschaussette', 'amaury.sanschaussette@gmail.com', '$2y$10$OhYWjtLKYrtoI8WkLFG/6.yBeSP9TIGxhDpzpGNNnoWP3Ttt/WjFq', 'user', NULL, NULL, NULL, NULL),
(3, 'Adrien', 'Lardon', 'adrien.lardon@gmail.fr', '$2y$10$gS1M.nkVo60FoMD86L2UV.pdMlQP5VqS/.gZbK8URyJl0bf.GXKhq', 'user', NULL, NULL, NULL, NULL),
(4, 'Woodis', 'Stephlane', 'woodis.stephlane@yahoo.com', '$2y$10$QAR9dVr1s0o527uy86645.ElU2J.CMzqhctLpZbGpIOJPxlBs5vhy', 'user', NULL, NULL, NULL, NULL),
(5, 'Compte', 'Admin', 'compte.admin@gmail.fr', '$2y$10$lmgjvcY/6cPp7HjfIZ5KLuvXTbsWl8p65.XF4oNrOcDWBM29yEdJm', 'admin', 'b5024ca7d17ab6cd746c830c96008e67282de767f429d830baf29b071aef604273d14b988bc75d473ba3d2cf836346210d4355979e385fd813c29bb0e81c4bab', '2025-11-06 00:51:22', NULL, NULL),
(6, 'Compte', 'User', 'compte.user@gmail.fr', '$2y$10$U06qrJGfI0qax6FrNmjgKe75qqe35gghQ3vpdeRxDh6oiMNsq7tRm', 'user', '564ed22673629f3a94c3a62b154cd02bd54ab10a52c6810d6182ad7cc9af5a1f754dc05a3df3125b2f2048189ebe56ff845033b0d55b41202f234372b57f08b5', '2025-11-14 21:31:06', 'TOTP', 'MZFLJBJX2O4775UP7ZEEF5QQCGTWUV6W');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`spectacle`) REFERENCES `spectacle` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
