import $ from "jquery";
import 'datatables.net';
import 'datatables.net-bs4';
import '../../vendor/omines/datatables-bundle/src/Resources/public/js/datatables';

export default function (reference) {
    const table = $(reference);
    table.initDataTables(table.data('settings'), {
        // add DataTable option here, cf. https://datatables.net/reference/option/
    });
};
