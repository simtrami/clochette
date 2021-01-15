import $ from 'jquery';

$('#withdraw-open-btn').on('click', function () {
    $('#modalWithdraw').modal('show');
});

$('#withdraw1').on('click', function () {
    const amount = $('#amount');
    amount.attr('value', '1');
    amount.val(1);
    amount.attr('step', 1);
    amount.attr('min', 1);
    $('#withdrawReason').attr('value', '1');
    $('#reasonDropdown').html("Retour d'écocup(s)");
});
$('#withdraw2').on('click', function () {
    const amount = $('#amount');
    amount.attr('value', '0');
    amount.val(0);
    amount.attr('step', 0.01);
    amount.attr('min', 0);
    $('#withdrawReason').attr('value', '2');
    $('#reasonDropdown').html('Autre');
});

$('#withdraw-save-btn').on('click', function () {
    const withdrawReason = $('#withdrawReason');
    if (withdrawReason.val() !== '2') {
        withdrawReason.attr('value', '1');
    }
    $('.qte').each(function () {
        $(this).attr('value', '0');
        $(this).val('0');
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
});