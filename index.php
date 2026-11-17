<?php
require_once 'functions.php';

// Déjà connecté : direction la liste
if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
// Pré-remplissage avec le cookie du dernier login
$login = $_COOKIE['dernier_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';

    if (isset(UTILISATEURS[$login]) && UTILISATEURS[$login] === $mdp) {
        session_regenerate_id(true);
        $_SESSION['login'] = $login;
        // Cookie valable 7 jours
        setcookie('dernier_user', $login, time() + 7 * 24 * 3600, '/');
        header('Location: liste.php');
        exit;
    }
    $erreur = 'Login ou mot de passe incorrect.';
}

$titre = 'Connexion';
include 'header.php';
?>

<section class="carte carte-etroite">
    <h1>Connexion</h1>

    <?php if ($erreur !== ''): ?>
        <p class="message erreur"><?= h($erreur) ?></p>
    <?php endif; ?>

    <form method="post" action="index.php">
        <label for="login">Login</label>
        <input type="text" id="login" name="login" value="<?= h($login) ?>" required autofocus>

        <label for="mdp">Mot de passe</label>
        <input type="password" id="mdp" name="mdp" required>

        <button type="submit" class="bouton">Se connecter</button>
    </form>
    <p class="aide">Compte : ouahiba / ouahiba123</p>

<?php include 'footer.php'; ?>
