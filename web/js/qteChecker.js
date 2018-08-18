$(".btn-value").on("click", function () {

    const button = $(this);
    const qteInput = button.parent().parent().find(".qte");
    let oldValue = button.parent().parent().find("input").val();
    const qteMax = button.parent().find(".qteStock").val();
    let newVal = parseFloat(0);

    if (button.attr("id") == "plus") {
        if (parseFloat(oldValue) < parseFloat(qteMax)){
            newVal = parseFloat(oldValue) + 1;
            //console.log(qteMax);
        } else {
            newVal = parseFloat(qteMax);
            //console.log(qteMax);
        }
    } else if (button.attr("id") == "moins") {
        // Don't allow decrementing below zero
        if (oldValue > 0) {
            newVal = parseFloat(oldValue) - 1;
            //console.log(newVal);
        } else {
            newVal = 0;
        }
    }

    qteInput.attr("value", newVal);
    qteInput.val(newVal);
    //console.log(button.parent().parent().find(".qte").val());

    let tot = 0;
    $(".qte").each(function () {
        let prix = $(this).parent().parent().find("#prix").val();
        tot = Math.round((tot + parseFloat($(this).val()) * parseFloat(prix)) * 100) / 100;
    });
    // $("#total").attr("value", tot);
    $("#totalForm").attr("value", tot);
    $('#totalTxt').html(tot);
});

$(".qte").on("input", function () {

    let tot = 0;
    
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
        tot = Math.round((tot + qte * prix) * 100 ) / 100;
    });
    // $("#total").attr("value", tot);
    $("#totalForm").attr("value", tot);
    $('#totalTxt').html(tot);
});