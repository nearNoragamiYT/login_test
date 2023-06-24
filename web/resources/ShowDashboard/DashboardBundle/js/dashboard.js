$(init);

function init() {
    $(".edition").on("click", function(){
        var path = path_event_edition + "/" + $(this).attr("event") + "/" + $(this).attr("edition")
        $(location).attr('href', path);
    });
    
    $("#search").keyup(function (e) {
        if (e.keyCode == 13) {
            if ($(this).val().length) {
                searchByText();
            } else {
                $(".edition").show();
                $(".no-match").hide();
            }

        }
    });

    $("#search").focusout(function () {
        if ($(this).val().length == 0) {
            $(".edition").show();
            $(".no-match").hide();
        }
    });
}

function searchByText() {
    $(".edition").hide();
    var textToFind = $('#search').val().toLowerCase().split(" ");
    $.each($('.edition'), function (i, item) {
        var formText = $(item).text().toLowerCase();
        var result = true;
        $.each(textToFind, function (j, text) {
            if (formText.indexOf(text) == -1) {
                result = false;
                return false;
            }
        });
        if (result) {
            $(item).fadeIn();
        }
        if ($(item).parents(".event-section").find(".edition:visible").length) {
            $(item).parents(".event-section").find(".no-match").hide();
        } else {
            $(item).parents(".event-section").find(".no-match").show();
        }
    });
}


