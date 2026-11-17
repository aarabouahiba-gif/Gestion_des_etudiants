<?php $titre = $titre ?? 'Gestion des étudiants'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titre) ?> – Gestion des étudiants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="entete">
    <div class="conteneur entete-contenu">
        <a class="logo" href="liste.php">Gestion des étudiants</a>
        <?php if (connecte()): ?>
            <nav class="menu">
                <a href="liste.php">Liste</a>
                <a href="ajouter.php">Ajouter</a>
                <span class="utilisateur"><?= h($_SESSION['login']) ?></span>
                <a href="logout.php">Déconnexion</a>
            </nav>
        <?php endif; ?>
    </div>
</header>
<main class="conteneur">
