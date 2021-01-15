import $ from "jquery";

function setPaymentMethod(methode) {
    const withdrawReason = $('#withdrawReason');
    $('#methode-paiement').attr('value', methode);

    if (methode === 'account') {
        if (withdrawReason.val() !== '0') {
            withdrawReason.attr('value', '0');
            $('#totalForm').attr('value', '0');
            $('#totalTxt').html('0');
        }
        if ($('#selectedAccount').val() === '') {
            openAndFocus('#modalAccount', '#paying-account');
        } else {
            $('#collapseSelectedAccount').collapse('show');
        }
    } else {
        if (withdrawReason.val() !== '0') {
            withdrawReason.attr('value', '0');
            $('#totalForm').attr('value', '0');
            $('#totalTxt').html('0');
        }
        $('#collapseSelectedAccount').collapse('hide');
    }
}

$('#methodCB').on('click', function () {
    setPaymentMethod('card');
})
$('#methodCash').on('click', function () {
    setPaymentMethod('cash');
})
$('#methodAccount').on('click', function () {
    setPaymentMethod('account');
})
$('#methodPumpkin').on('click', function () {
    setPaymentMethod('pumpkin');
})

$('#account-save-btn').on('click', function () {
    $('#selectedAccount').attr('value', $('#paying-account').val());
    $('#collapseSelectedAccount').collapse('show');
    $('#modalAccount').modal('hide');
});