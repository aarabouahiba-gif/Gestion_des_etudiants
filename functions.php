<?php
// functions.php : constantes, session et fonctions communes à toutes les pages

session_start();

// ---------- Constantes ----------
const FICHIER         = __DIR__ . '/data/etudiants.txt';
const DOSSIER_PHOTOS  = __DIR__ . '/uploads/';
const FILIERES        = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS    = ['ouahiba' => 'ouahiba123', 'prof' => 'prof2026'];
const EXTENSIONS_OK   = ['jpg', 'jpeg', 'png'];
const TAILLE_MAX_PHOTO = 1048576; // 1 Mo

// ---------- Sécurité et connexion ----------

/** Protège l'affichage contre les injections HTML. */
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** Retourne true si un utilisateur est connecté. */
function connecte(): bool
{
    return isset($_SESSION['login']);
}

/** Redirige vers index.php si l'utilisateur n'est pas connecté. */
function exiger_connexion(): void
{
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

// ---------- Messages flash ----------

function flash(string $message, string $type = 'succes'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// ---------- Stockage dans le fichier texte ----------

/** Supprime espaces inutiles, points-virgules et retours à la ligne. */
function nettoyer(string $s): string
{
    $s = str_replace([';', "\r", "\n"], ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

/** Lit le fichier et retourne un tableau associatif indexé par l'id. */
function lire_etudiants(): array
{
    $liste = [];
    if (!file_exists(FICHIER)) {
        return $liste;
    }
    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $p = explode(';', $ligne);
        if (count($p) < 5) {
            continue;
        }
        $id = (int)$p[0];
        $liste[$id] = [
            'id'      => $id,
            'nom'     => $p[1],
            'prenom'  => $p[2],
            'filiere' => $p[3],
            'note'    => (float)$p[4],
            'photo'   => $p[5] ?? '',
        ];
    }
    return $liste;
}

/** Réécrit tout le fichier à partir du tableau. */
function sauver_etudiants(array $liste): void
{
    $lignes = [];
    foreach ($liste as $e) {
        $lignes[] = implode(';', [
            $e['id'], $e['nom'], $e['prenom'], $e['filiere'], $e['note'], $e['photo'],
        ]);
    }
    $contenu = $lignes ? implode("\n", $lignes) . "\n" : '';
    file_put_contents(FICHIER, $contenu, LOCK_EX);
}

/** Supprime la photo d'un étudiant si elle existe. */
function supprimer_photo(string $photo): void
{
    if ($photo === '') {
        return;
    }
    $chemin = DOSSIER_PHOTOS . basename($photo);
    if (file_exists($chemin)) {
        unlink($chemin);
    }
}
