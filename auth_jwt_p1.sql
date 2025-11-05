-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : mer. 05 nov. 2025 à 19:52
-- Version du serveur : 5.7.39
-- Version de PHP : 8.2.0

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

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `spectacle` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(8, 6, 2, '2025-11-09');

-- --------------------------------------------------------

--
-- Structure de la table `spectacle`
--

CREATE TABLE `spectacle` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `director` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(25) NOT NULL DEFAULT 'user',
  `refresh_token` varchar(255) DEFAULT NULL,
  `refresh_token_expires_at` datetime DEFAULT NULL,
  `two_factor_method` enum('none','email','sms','otp') DEFAULT 'none',
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `password`, `role`, `refresh_token`, `refresh_token_expires_at`, `two_factor_method`, `two_factor_secret`, `phone`) VALUES
(1, 'STÉPHANE', 'DESCARPENTRIES', 'stephane.descarpentries@hotmail.fr', '$2y$10$fBhWaLbAewKciFCVjpTwNuF/IGmB8VP2Vqdj1A6D1jD7NCbmHaA4e', 'admin', '66f1bbbc30496bb6697bd46b0e58975781d34700f8d04b3bce9f21ba7e3176822c13915317aae567ff14f193fc268d031fddd2f287c93464b160440ef8e55fed', '2025-11-06 00:20:56', 'none', NULL, NULL),
(2, 'Amaury', 'Sanschaussette', 'amaury.sanschaussette@gmail.com', '$2y$10$OhYWjtLKYrtoI8WkLFG/6.yBeSP9TIGxhDpzpGNNnoWP3Ttt/WjFq', 'user', '87d75f12c5355efc8aba4621fb6623ac8bfa522b8a8c8bd8aef1b433d1568c8dca13e4e0f75d49954a964a659cb3ef0327e6dd53d3ee0bde162cffea1848b32f', '2025-11-07 14:15:25', 'none', NULL, NULL),
(3, 'Adrien', 'Lardon', 'adrien.lardon@gmail.fr', '$2y$10$gS1M.nkVo60FoMD86L2UV.pdMlQP5VqS/.gZbK8URyJl0bf.GXKhq', 'user', '8f3cec142391a382aa96cd6020d49a13b3d071ce725c05baab1a7c376607a3dbf07e05023d91246ed9739bfdb5d880f1b572721acdb2b37826d955ea1f7b29e8', '2025-11-12 19:49:21', 'none', NULL, NULL),
(4, 'Woodis', 'Stephlane', 'woodis.stephlane@yahoo.com', '$2y$10$QAR9dVr1s0o527uy86645.ElU2J.CMzqhctLpZbGpIOJPxlBs5vhy', 'user', NULL, NULL, 'none', NULL, NULL),
(5, 'Compte', 'Admin', 'compte.admin@gmail.fr', '$2y$10$lmgjvcY/6cPp7HjfIZ5KLuvXTbsWl8p65.XF4oNrOcDWBM29yEdJm', 'admin', 'b5024ca7d17ab6cd746c830c96008e67282de767f429d830baf29b071aef604273d14b988bc75d473ba3d2cf836346210d4355979e385fd813c29bb0e81c4bab', '2025-11-06 00:51:22', 'none', NULL, NULL),
(6, 'Compte', 'User', 'compte.user@gmail.fr', '$2y$10$U06qrJGfI0qax6FrNmjgKe75qqe35gghQ3vpdeRxDh6oiMNsq7tRm', 'user', '37be9fe00a4ceeba86b701e8a7651a36398d63b9d79e1dc3ae021f3b129e2dfd93258bc0a595ae09e5a901c164ccc9d82ffc8e4467d4419ce59a5847ddd49955', '2025-11-07 14:22:45', 'none', NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `spectacle` (`spectacle`);

--
-- Index pour la table `spectacle`
--
ALTER TABLE `spectacle`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `spectacle`
--
ALTER TABLE `spectacle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
