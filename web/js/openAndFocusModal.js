function openAndFocus(modal, input) {
    $(modal).modal('show');
    setTimeout(function (){
        $(input).focus();
    }, 600);
}