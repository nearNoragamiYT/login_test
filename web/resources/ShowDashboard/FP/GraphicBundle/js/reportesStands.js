
$(document).ready(function () {
    $("#excel-stands-total").on('click', function () {
        exportTotalStands();
    });
    
    $("#excel-stands-edicion").on('click', function () {
        exportEditionStands();
    });
});

function exportTotalStands(){
    window.location.replace(url_stands_total);
}

function exportEditionStands(){
    window.location.replace(url_stands_edition.replace("0000","000"));
}