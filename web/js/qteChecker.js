$(".btn-value").on("click", function () {

    var button = $(this);
    var qte = button.parent().parent().find(".qte");
    var oldValue = button.parent().parent().find("input").val();
    var qteMax = button.parent().find(".qteStock").val();
    var newVal = parseFloat(0);

    if (button.text() == "+") {
        if (parseFloat(oldValue) < parseFloat(qteMax)){
            newVal = parseFloat(oldValue) + 1;
            //console.log(qteMax);
        } else {
            newVal = parseFloat(qteMax);
            //console.log(qteMax);
        }
    } else {
        // Don't allow decrementing below zero
        if (oldValue > 0) {
            newVal = parseFloat(oldValue) - 1;
            //console.log(newVal);
        } else {
            newVal = 0;
        }
    }

    button.parent().parent().find(".qte").attr("value", newVal);
    button.parent().parent().find(".qte").val(newVal);
    //console.log(button.parent().parent().find(".qte").val());

    var tot = 0;
    $(".qte").each(function () {
        var prix = $(this).parent().parent().find("#prix").val();
        tot = tot + parseFloat($(this).val()) * parseFloat(prix);
    });
    $("#total").attr("value", tot);
});

$(".qte").on("input", function () {

    var tot = 0;
    
    $(".qte").each(function () {
        var qte = parseFloat($(this).val());
        var qteMax = parseFloat($(this).parent().find(".qteStock").val());
        var prix = parseFloat($(this).parent().parent().find("#prix").val());

        if (qte < 0) {
            $(this).attr("value", 0);
            $(this).val(0);
            qte = 0;
        } else if (qte > qteMax) {
            $(this).attr("value", qteMax);
            $(this).val(qteMax);
            qte = qteMax;
        }
        tot = tot + qte * prix;
    });
    $("#total").attr("value", tot);
});