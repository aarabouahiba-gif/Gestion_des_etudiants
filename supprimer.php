<?php
require_once 'functions.php';
exiger_connexion();

$id    = (int)($_GET['id'] ?? 0);
$liste = lire_etudiants();

if ($id > 0 && isset($liste[$id])) {
    supprimer_photo($liste[$id]['photo']);
    unset($liste[$id]);
    sauver_etudiants($liste);
    flash('Étudiant supprimé.');
} else {
    flash('Étudiant introuvable.', 'erreur');
}

header('Location: liste.php');
exit;
