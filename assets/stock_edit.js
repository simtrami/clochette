import './styles/stock.scss';
import $ from 'jquery';

$("#stocks_type").change(function () {
    const volumeRow = $("#volume-row");
    const stocksVolume = $("#stocks_volume");
    let selectedType = $(this).find('option:selected').text();
    if (selectedType === "Fût" || selectedType === "Bouteille") {
        volumeRow.collapse('show');
        stocksVolume.attr('disable', 'false');
    } else {
        volumeRow.collapse('hide');
        stocksVolume.attr('disable', 'true');
    }
});
