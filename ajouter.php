<?php
require_once 'functions.php';
exiger_connexion();

$liste = lire_etudiants();

// Mode modification si ?id=... est présent
$id    = (int)($_GET['id'] ?? 0);
$modif = $id > 0;

if ($modif && !isset($liste[$id])) {
    flash('Étudiant introuvable.', 'erreur');
    header('Location: liste.php');
    exit;
}

$vals = $modif
    ? ['nom' => $liste[$id]['nom'], 'prenom' => $liste[$id]['prenom'], 'filiere' => $liste[$id]['filiere'], 'note' => (string)$liste[$id]['note']]
    : ['nom' => '', 'prenom' => '', 'filiere' => '', 'note' => ''];
$photoActuelle = $modif ? $liste[$id]['photo'] : '';
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vals['nom']     = nettoyer($_POST['nom'] ?? '');
    $vals['prenom']  = nettoyer($_POST['prenom'] ?? '');
    $vals['filiere'] = $_POST['filiere'] ?? '';
    $vals['note']    = str_replace(',', '.', trim($_POST['note'] ?? ''));

    // ----- Validation des champs -----
    if ($vals['nom'] === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($vals['prenom'] === '') {
        $erreurs[] = 'Le prénom est obligatoire.';
    }
    if (!in_array($vals['filiere'], FILIERES, true)) {
        $erreurs[] = 'Veuillez choisir une filière valide.';
    }
    if (!is_numeric($vals['note']) || $vals['note'] < 0 || $vals['note'] > 20) {
        $erreurs[] = 'La note doit être un nombre entre 0 et 20.';
    }

    // Bonus : refuser un doublon nom + prénom
    if ($vals['nom'] !== '' && $vals['prenom'] !== '') {
        foreach ($liste as $e) {
            if ($e['id'] !== $id
                && strcasecmp($e['nom'], $vals['nom']) === 0
                && strcasecmp($e['prenom'], $vals['prenom']) === 0) {
                $erreurs[] = 'Cet étudiant existe déjà.';
                break;
            }
        }
    }

    // ----- Validation de la photo (facultative) -----
    $envoi = false;
    $ext   = '';
    $f     = $_FILES['photo'] ?? null;
    if ($f !== null && $f['error'] !== UPLOAD_ERR_NO_FILE) {
        $envoi = true;
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = 'Erreur lors de l\'envoi de la photo (code ' . $f['error'] . ').';
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, EXTENSIONS_OK, true)) {
                $erreurs[] = 'Photo : extensions autorisées jpg, jpeg, png.';
            } elseif ($f['size'] > TAILLE_MAX_PHOTO) {
                $erreurs[] = 'Photo : taille maximale 1 Mo.';
            } elseif (@getimagesize($f['tmp_name']) === false) {
                $erreurs[] = 'Photo : le fichier n\'est pas une image valide.';
            }
        }
    }

    // ----- Enregistrement -----
    if (empty($erreurs)) {
        $photo = $photoActuelle;

        if ($envoi) {
            $nouveau = uniqid('etu_') . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], DOSSIER_PHOTOS . $nouveau)) {
                supprimer_photo($photoActuelle); // supprime l'ancienne
                $photo = $nouveau;
            } else {
                $erreurs[] = 'Impossible d\'enregistrer la photo.';
            }
        }
    }

    if (empty($erreurs)) {
        $nouvelId = $modif ? $id : ($liste ? max(array_keys($liste)) + 1 : 1);
        $liste[$nouvelId] = [
            'id'      => $nouvelId,
            'nom'     => $vals['nom'],
            'prenom'  => $vals['prenom'],
            'filiere' => $vals['filiere'],
            'note'    => round((float)$vals['note'], 2),
            'photo'   => $photo,
        ];
        sauver_etudiants($liste);
        flash($modif ? 'Étudiant modifié avec succès.' : 'Étudiant ajouté avec succès.');
        header('Location: liste.php');
        exit;
    }
}

$titre = $modif ? 'Modifier un étudiant' : 'Ajouter un étudiant';
include 'header.php';
?>

<section class="carte carte-etroite">
    <h1><?= h($titre) ?></h1>

    <?php if (!empty($erreurs)): ?>
        <div class="message erreur">
            <ul>
                <?php foreach ($erreurs as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" action="ajouter.php<?= $modif ? '?id=' . $id : '' ?>">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="<?= h($vals['nom']) ?>">

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= h($vals['prenom']) ?>">

        <label for="filiere">Filière</label>
        <select id="filiere" name="filiere">
            <option value="">-- Choisir --</option>
            <?php foreach (FILIERES as $fil): ?>
                <option value="<?= h($fil) ?>" <?= $vals['filiere'] === $fil ? 'selected' : '' ?>><?= h($fil) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="note">Note (0 à 20)</label>
        <input type="text" id="note" name="note" value="<?= h($vals['note']) ?>" inputmode="decimal">

        <label for="photo">Photo (facultative · jpg, png · 1 Mo max)</label>
        <?php if ($photoActuelle !== '' && file_exists(DOSSIER_PHOTOS . basename($photoActuelle))): ?>
            <img class="photo photo-grande" src="uploads/<?= h(basename($photoActuelle)) ?>" alt="Photo actuelle">
        <?php endif; ?>
        <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">

        <div class="boutons">
            <button type="submit" class="bouton"><?= $modif ? 'Enregistrer' : 'Ajouter' ?></button>
            <a href="liste.php" class="bouton bouton-gris">Annuler</a>
        </div>
    </form>
</section>

<?php include 'footer.php'; ?>
