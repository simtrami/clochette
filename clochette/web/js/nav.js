jQuery.ajaxSetup({

    beforeSend: function() {

        $('#loading').show();

    },

    complete: function(){

        $('#loading').hide();

    },

});



/* $(document).ready(function () {

    $('.menu a').click(function () {

        page = $(this).attr('href');

        $.ajax({

            url: '../index.php?rooting=' + page,

            cache: false,

            success: function (html) {

                display(html);

            },

            error: function (XMLHttpRequest, textStatus, errorThrown) {

                alert(textStatus);

            }

        });

        return false;

    });

}); */



function display(data) {

    $('body').empty();

    $('body').append(data);



};



