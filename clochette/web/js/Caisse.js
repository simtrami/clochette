$(document).ready(function () {

    $('.add-delete-article a').click(function (event) {
        event.preventDefault();
        page = $(this).attr('id');
        id=page.substring(0,1);
        op=page.substring(1,2);
        url2='controller/panier.ctrl.php?id='+id+'&op='+op;

        $.ajax({
           type:'GET',
           url:url2,
           success : function (data) {

           },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(textStatus);
            }
        });
    });
});

function display2(data){
    $('.caisseResults').empty();
    $('.caisseResults').append(data);
}
