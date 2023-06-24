$(init);

function init() {
    tabPermisos();
}

function tabPermisos() {
    var permisos = "", keys = "", total = "", selector = "";
    if (tabPermission != null) {
        if (typeof (tabPermission[currentRoute]) != undefined && tabPermission[currentRoute] != undefined && tabPermission[currentRoute] != "") {
            permisos = tabPermission[currentRoute];
            keys = Object.keys(permisos);
            total = keys.length;

            for (var i = 0; i < total; i++) {
                if (permisos[keys[i]]["type"] == "id")
                    selector = "#" + keys[i];
                if (permisos[keys[i]]["type"] == "class")
                    selector = "." + keys[i];

                if (permisos[keys[i]]["attr"] == "attr")
                    $(selector).attr(permisos[keys[i]]["prop"], permisos[keys[i]]["value"]);
                if (permisos[keys[i]]["attr"] == "css")
                    $(selector).css(permisos[keys[i]]["prop"], permisos[keys[i]]["value"]);
            }
        }
    }

}
