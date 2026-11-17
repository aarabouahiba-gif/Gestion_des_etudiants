// script.js : confirmation de suppression et recherche instantanée

document.addEventListener('DOMContentLoaded', function () {

    // 1. Confirmation avant de suivre un lien de suppression
    document.querySelectorAll('a.suppr').forEach(function (lien) {
        lien.addEventListener('click', function (event) {
            if (!confirm('Voulez-vous vraiment supprimer cet étudiant ?')) {
                event.preventDefault();
            }
        });
    });

    // 2. Recherche instantanée dans le tableau
    var champ = document.querySelector('#q');
    var lignes = document.querySelectorAll('#tableau tbody tr.ligne');

    if (champ) {
        champ.addEventListener('input', function () {
            var texte = champ.value.trim().toLowerCase();
            lignes.forEach(function (ligne) {
                var contenu = ligne.dataset.recherche || '';
                ligne.style.display = contenu.indexOf(texte) !== -1 ? '' : 'none';
            });
        });
    }
});
