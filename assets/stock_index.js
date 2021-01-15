import './styles/stock.scss';
import dtInit from './components/datatable-options';

dtInit('#table-drafts', [[1, 'asc']], [{"orderable": false, "targets": 5}]);
dtInit('#table-bottles', [[1, 'asc']], [{"orderable": false, "targets": 5}]);
dtInit('#table-articles', [[1, 'asc']], [{"orderable": false, "targets": 5}]);
