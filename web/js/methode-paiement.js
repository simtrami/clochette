function choixPaiement(methode) {
    $("#methode-paiement").attr("value", methode);
    if (methode !== "account"){
        $("#collapseSelectedAccount").collapse("hide");
    } else if (methode === "account"){
        if ($("#selectedAccount").val() == ""){
            $("#modalAccount").modal('show');
        } else {
            $("#collapseSelectedAccount").collapse("toggle");
        }
    }
}

function saveAccount() {
    $("#selectedAccount").attr('value', $("#paying-account").val());
    $("#collapseSelectedAccount").collapse('show');
    $("#modalAccount").modal('hide');
}