function choixPaiement(methode) {
    $("#methode-paiement").attr("value", methode);
    if (methode !== "account"){
        $(".collapse").collapse("hide")
    }
}