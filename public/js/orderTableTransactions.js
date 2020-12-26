$(document).ready(function() {
$.fn.dataTable.moment( 'HH:mm:ss DD/MM/YYYY' );

$('#table').DataTable( {
    "language": {
            "decimal":        ".",
            "emptyTable":     "Aucune donnée disponible dans le tableau",
            "info": "Page _PAGE_ sur _PAGES_",
            "infoEmpty":      "Aucune entrée disponible",
            "infoFiltered":   "(d'après les _MAX_ entrées)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Afficher _MENU_ entrées par page",
            "loadingRecords": "Chargement...",
            "processing":     "En traitement...",
            "search":         "Rechercher:",
            "zeroRecords":    "Aucune entrée correspondante trouvée",
            "paginate": {
                "first":      "Début",
                "last":       "Fin",
                "next":       "Suivante",
                "previous":   "Précédente"
            },
            "aria": {
                "sortAscending":  ": activer pour trier dans l'ordre croissant",
                "sortDescending": ": activer pour trier dans l'ordre décroissant"
            }
        },
    "order": [[5, 'desc']],
    "columnDefs": [
        { "orderable": false, "targets": 6 }
    ]
    } );
} );
