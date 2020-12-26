function choixPaiement(methode) {
    const withdrawReason = $('#withdrawReason');
    $('#methode-paiement').attr('value', methode);
    if (methode !== 'account'){
        if (withdrawReason.val() !== '0'){
            withdrawReason.attr('value', '0');
            $('#totalForm').attr('value', '0');
            $('#totalTxt').html('0');
        }
        $('#collapseSelectedAccount').collapse('hide');
    } else if (methode === 'account'){
        if (withdrawReason.val() !== '0'){
            withdrawReason.attr('value', '0');
            $('#totalForm').attr('value', '0');
            $('#totalTxt').html('0');
        }
        if ($('#selectedAccount').val() === ''){
            openAndFocus('#modalAccount', '#paying-account');
        } else {
            $('#collapseSelectedAccount').collapse('show');
        }
    }
}

function saveAccount() {
    $('#selectedAccount').attr('value', $('#paying-account').val());
    $('#collapseSelectedAccount').collapse('show');
    $('#modalAccount').modal('hide');
}
