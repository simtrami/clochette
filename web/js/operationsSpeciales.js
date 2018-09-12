$('#withdraw1').on('click', function () {
    const amount = $('#amount');
    amount.attr('value', '1');
    amount.val(1);
    amount.prop('readonly', true);
    $('#withdrawReason').attr('value', '1');
    $('#reasonDropdown').html("Retour d'écocup");
    $('#collapseRefundMethod').collapse('show');
});
$('#withdraw2').on('click', function () {
    const amount = $('#amount');
    amount.attr('value', '0');
    amount.val(0);
    amount.prop('readonly', false);
    $('#withdrawReason').attr('value', '2');
    $('#reasonDropdown').html('Autre');
    $('#collapseRefundMethod').collapse('show');
});

function saveWithdraw() {
    const qte = $('.qte');
    qte.each(function () {
        qte.attr('value', '0');
        qte.val('0');
    });
    const amount = $('#amount');
    $('#totalForm').attr('value', -parseFloat(amount.val()));
    $('#totalTxt').html(-parseFloat(amount.val()));

    if (document.getElementById('selectedRefundMethod').value === 'account') {
        $('#methode-paiement').attr('value', 'account');
        $('#modalWithdraw').modal('hide');
        openAndFocus('#modalAccount', '#paying-account');
    } else {
        $('#methode-paiement').attr('value', 'cash');
        $('#modalWithdraw').modal('hide');
    }
}
