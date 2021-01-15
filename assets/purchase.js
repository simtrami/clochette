import './styles/purchase.scss';

import './components/specific-operations';
import './components/check-quantity';
import './components/payment-methode';
import './components/open-and-focus-modal';
import './components/algolia-autocomplete';

// TODO: https://symfonycasts.com/screencast/webpack-encore/add-style-entry#play
import $ from "jquery";

$(document).ready(function () {
    $('#paying-account').keypress(function (e) {
        if (e.keyCode === 13)
            $('.validate-account').click();
    });
    $('.amount-withdraw').keypress(function (e) {
        if (e.keyCode === 13)
            $('.validate-withdraw').click();
    });
});
