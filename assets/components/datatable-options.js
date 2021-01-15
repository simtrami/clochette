import $ from "jquery";
import 'datatables.net';
import 'datatables.net-bs4';
/*import './components/datatable-datetime-moment';

$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');*/

// TODO: use AJAX
export default function (reference, order = null, columnDefs = null) {
    $(reference).DataTable({
        "language": {
            "decimal": ".",
            "emptyTable": "Aucune donnée disponible dans le tableau",
            "info": "Page _PAGE_ sur _PAGES_",
            "infoEmpty": "Aucune entrée disponible",
            "infoFiltered": "(d'après les _MAX_ entrées)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Afficher _MENU_ entrées par page",
            "loadingRecords": "Chargement...",
            "processing": "En traitement...",
            "search": "Rechercher:",
            "zeroRecords": "Aucune entrée correspondante trouvée",
            "paginate": {
                "first": "Début",
                "last": "Fin",
                "next": "Suivante",
                "previous": "Précédente"
            },
            "aria": {
                "sortAscending": ": activer pour trier dans l'ordre croissant",
                "sortDescending": ": activer pour trier dans l'ordre décroissant"
            }
        },
        "order": order !== null ? order : [],
        "columnDefs": columnDefs
    });
}