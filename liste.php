<?php
require_once 'functions.php';
exiger_connexion();

$liste = lire_etudiants();

// Message flash : lu puis supprimé
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ---------- Recherche ----------
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $liste = array_filter($liste, function (array $e) use ($q): bool {
        return stripos($e['nom'], $q) !== false || stripos($e['prenom'], $q) !== false;
    });
}

// ---------- Tri ----------
$tris = ['ajout' => 'Ordre d\'ajout', 'nom' => 'Nom', 'filiere' => 'Filière', 'note' => 'Note (décroissante)'];
$tri  = $_GET['tri'] ?? 'ajout';
if (!in_array($tri, array_keys($tris), true)) {
    $tri = 'ajout';
}

if ($tri === 'nom') {
    usort($liste, fn($a, $b) => strcasecmp($a['nom'], $b['nom']));
} elseif ($tri === 'filiere') {
    usort($liste, fn($a, $b) => strcasecmp($a['filiere'], $b['filiere']) ?: strcasecmp($a['nom'], $b['nom']));
} elseif ($tri === 'note') {
    usort($liste, fn($a, $b) => $b['note'] <=> $a['note']);
} else {
    $liste = array_values($liste); // ordre d'ajout
}

// ---------- Statistiques ----------
$nombre  = count($liste);
$notes   = array_column($liste, 'note');
$moyenne = $nombre > 0 ? array_sum($notes) / $nombre : 0;
$meilleure = $nombre > 0 ? max($notes) : null;

$titre = 'Liste des étudiants';
include 'header.php';
?>

<h1>Liste des étudiants</h1>

<?php if ($flash): ?>
    <p class="message <?= h($flash['type']) ?>"><?= h($flash['message']) ?></p>
<?php endif; ?>

<form method="get" action="liste.php" class="filtres carte">
    <div>
        <label for="q">Recherche</label>
        <input type="search" id="q" name="q" value="<?= h($q) ?>" placeholder="Nom ou prénom">
    </div>
    <div>
        <label for="tri">Trier par</label>
        <select id="tri" name="tri">
            <?php foreach ($tris as $cle => $libelle): ?>
                <option value="<?= h($cle) ?>" <?= $tri === $cle ? 'selected' : '' ?>><?= h($libelle) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bouton">Appliquer</button>
</form>

<div class="stats">
    <div class="stat"><span class="stat-valeur"><?= $nombre ?></span><span class="stat-titre">Étudiants affichés</span></div>
    <div class="stat"><span class="stat-valeur"><?= number_format($moyenne, 2, ',', ' ') ?></span><span class="stat-titre">Moyenne</span></div>
    <div class="stat"><span class="stat-valeur"><?= $meilleure !== null ? number_format($meilleure, 2, ',', ' ') : '–' ?></span><span class="stat-titre">Meilleure note</span></div>
</div>

<div class="carte carte-tableau">
<table id="tableau">
    <thead>
        <tr>
            <th>Photo</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Filière</th>
            <th>Note</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($liste)): ?>
        <tr><td colspan="6" class="vide">Aucun étudiant</td></tr>
    <?php else: ?>
        <?php foreach ($liste as $e): ?>
            <?php $classeNote = $e['note'] >= 10 ? 'note-ok' : 'note-ko'; ?>
            <tr class="ligne" data-recherche="<?= h(strtolower($e['nom'] . ' ' . $e['prenom'])) ?>">
                <td>
                    <?php if ($e['photo'] !== '' && file_exists(DOSSIER_PHOTOS . basename($e['photo']))): ?>
                        <img class="photo" src="uploads/<?= h(basename($e['photo'])) ?>" alt="Photo de <?= h($e['prenom']) ?>">
                    <?php else: ?>
                        <span class="photo photo-vide"><?= h(strtoupper(mb_substr($e['prenom'], 0, 1))) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= h(strtoupper($e['nom'])) ?></td>
                <td><?= h(ucfirst($e['prenom'])) ?></td>
                <td><span class="badge"><?= h($e['filiere']) ?></span></td>
                <td class="<?= $classeNote ?>"><?= number_format($e['note'], 2, ',', '') ?></td>
                <td class="actions">
                    <a href="ajouter.php?id=<?= (int)$e['id'] ?>">Modifier</a>
                    <a href="supprimer.php?id=<?= (int)$e['id'] ?>" class="suppr">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php include 'footer.php'; ?>
