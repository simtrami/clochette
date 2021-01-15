import $ from "jquery";

global.openAndFocus = function openAndFocus(modal, input) {
    $(modal).modal('show');
    setTimeout(function () {
        $(input).focus();
    }, 600);
}
