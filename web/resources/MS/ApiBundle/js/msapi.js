var msApiLastQuery = "";
var queue = null;
var queue = [];
var queueInterval = null;
var dumpQueueTime = 5000;
var sesionKey = "";
var Base64 = {
    // private property
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    // public method for encoding
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;
        input = Base64._utf8_encode(input);
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
        }
        return output;
    },
    // public method for decoding
    decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output + String.fromCharCode(chr1);
            if (enc3 !== 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 !== 64) {
                output = output + String.fromCharCode(chr3);
            }
        }
        output = Base64._utf8_decode(output);
        return output;
    },
    // private method for UTF-8 encoding
    _utf8_encode: function (string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },
    // private method for UTF-8 decoding
    _utf8_decode: function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while (i < utftext.length) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}
if (!inIframe())
    var msApiActionData = {};
$(document).ready(function () {
    sesionKey = getRandomInt(1000, 2000) + "_" + (new Date().getTime()) + "_" + getRandomInt(0, 99);
    if (!inIframe())
        msApiVisit();

});
window.onbeforeunload = function (e) {
    if (!inIframe()) {
        var actionPath = "/msapi/leaving";
        $.ajax({
            type: "post",
            url: statsServer + actionPath,
            data: {msApiActionData: parent.msApiActionData},
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/j-son;charset=UTF-8");
                }
            },
            success: function (response) {
            },
            error: function (request, status, error) {
            },
            complete: function (msg)
            {
            }
        });
    }
}
function msApiVisit() {
    var msApiVisitData = new Object();
    var actionPath = "/msapi/visit";
    var user = $("#msapi-user").attr("data-reference");
    msApiVisitData["msUserId"] = (typeof user === "undefined" || user === 0 || user === '') ? 0 : user;
    msApiVisitData["x"] = $("#msapi-me").attr("data-reference");
    msApiVisitData["serLocation"] = location.origin + "" + location.pathname;
    if (typeof msApiVisitData["msUserId"] === "undefined" || msApiVisitData["msUserId"] === 0 || msApiVisitData["msUserId"] === '') {
        msApiVisitData["msUserId"] = 0;
    }
    if (typeof msApiVisitData["serLocation"] === "undefined") {
        msApiVisitData["serLocation"] = 0;
    }
    msApiVisitData["httpReferer"] = Base64.encode(document.referrer);
    msApiVisitData["edition"] = parent.edicion["idEdicion"];
    msApiVisitData["event"] = parent.edicion["idEvento"];
    msApiVisitData["key"] = sesionKey;
    var originUrl = location.origin + "" + location.pathname;
    originUrl = originUrl.replace("&view=1", "");
    originUrl = originUrl.replace("&view=2", "");
    msApiVisitData["originUrl"] = originUrl;
    $.ajax({
        type: "post",
        url: statsServer + actionPath,
        data: {msApiVisitData: msApiVisitData},
        beforeSend: function (x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/j-son;charset=UTF-8");
            }
        },
        success: function (response) {
        },
        error: function (request, status, error) {
        },
        complete: function (msg)
        {
        }
    });
}

function msApiTrackElement(jQueryElement) {
    msApiParseDataAtributes(jQueryElement);
}

function msApiParseElements() {
    $("*[data-msapi='1']").each(function (index) {
        $(this).attr("data-msapi", "0");
        $(this).click(function () {
            msApiParseDataAtributes(this);
        });
    });
    $("*[data-msapi='2']").each(function (index) {
        $(this).attr("data-msapi", "0");
        if (!$(this).is("input"))
            $(this).click(function () {
                var query = $("#" + $(this).attr("data-q")).val();
                if (query.length < 2 || query === msApiLastQuery)
                    return;
                msApiLastQuery = query;
                msApiQuery(query);
            });
        var timer, delay = 2000; // tiempo para realizar la búsqueda
        if ($(this).is("input"))
            $(this).keypress(function (e) {
                var element = $("#" + $(this).attr("data-q"));
                clearTimeout(timer);
                timer = setTimeout(function () {
                    var query = $(element).val();
                    if (query.length < 2 || query === msApiLastQuery)
                        return;
                    msApiLastQuery = query;
                    msApiQuery(query);
                }, delay);
            });
    });
}
function msApiQuery(query, type, idRef) {
    var actionPath = "/msapi/search";
    var msApiSearchData = new Object();
    if (typeof type === "undefined")
        type = 0;
    if (typeof idRef === "undefined")
        idRef = 0;
    var user = $("#msapi-user").attr("data-reference");
    msApiSearchData["msUserId"] = (typeof user === "undefined" || user === 0 || user === '') ? 0 : user;
    msApiSearchData["x"] = $("#msapi-me").attr("data-reference");
    msApiSearchData["query"] = query.replace("&", "y");
    msApiSearchData["key"] = sesionKey;
    msApiSearchData["type"] = type;
    msApiSearchData["idRef"] = idRef;
    msApiSearchData["edition"] = parent.edicion["idEdicion"];
    msApiSearchData["event"] = parent.edicion["idEvento"];
    $.ajax({
        type: "post",
        url: statsServer + actionPath,
        data: {msApiSearchData: msApiSearchData},
        beforeSend: function (x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/j-son;charset=UTF-8");
            }
        },
        success: function (response) {
        },
        error: function (request, status, error) {
        },
        complete: function (msg)
        {
        }
    });
}

function msApiParseDataAtributes(element) {
    var idExpositor = $(element).attr("data-e");
    idExpositor = (msApiIsEmpty(idExpositor)) ? 0 : idExpositor;
    var object = $(element).attr("data-x");
    object = (msApiIsEmpty(object)) ? 0 : object;
    /* QUITAR PALABRA UNDEFINED DE query */
    if (object.substr(object.length - 9) === "undefined") {
        object = object.replace("undefined", "");
    }
    var valor = $(element).attr("data-v");
    valor = (msApiIsEmpty(valor)) ? 0 : valor;
    var reference = $("#msapi-user").attr("data-reference");
    reference = (msApiIsEmpty(reference)) ? 0 : reference;
    var parameters = new Object();
    parameters["e"] = idExpositor;
    parameters["x"] = object;
    parameters["r"] = reference;
    parameters["key"] = sesionKey;
    parameters["v"] = valor;
    parameters["amount"] = 1;
    parameters["edition"] = parent.edicion["idEdicion"];
    parameters["event"] = parent.edicion["idEvento"];
    parameters["key"] = sesionKey;
    if (!parent.msApiActionData.hasOwnProperty(idExpositor)) {
        parent.msApiActionData[idExpositor] = {};
    }
    if (!parent.msApiActionData[idExpositor].hasOwnProperty(object)) {
        parent.msApiActionData[idExpositor][object] = {};
    } else {
        if (object != 1) { // when the object is equals to booth because doesn't can to be merged
            parameters["amount"] = parent.msApiActionData[idExpositor][object]["values"]["amount"];
            parameters["amount"] += 1;
        }
    }
    if (object == 1) { // when the object is equals to booth because doesn't can to be merged
        if (!parent.msApiActionData[idExpositor][object].hasOwnProperty(valor)) {
            parent.msApiActionData[idExpositor][object][valor] = {};
        } else {
            parameters["amount"] = parent.msApiActionData[idExpositor][object][valor].values["amount"];
            parameters["amount"] += 1;
        }
        parent.msApiActionData[idExpositor][object][valor].values = parameters;
    } else
    {
        parent.msApiActionData[idExpositor][object]["values"] = parameters;
    }
}
function msApiTrackQuery(query, type, idRef) {
    msApiQuery(query, type, idRef);
}


//utilities
function msApiIsEmpty(element) {
    if (element === undefined || element === "" || element === null)
        return true;
    return false;
}
function replaceAll(str, replace, with_this)
{
    var str_hasil = "";
    for (var i = 0; i <= str.length; i++)
    {
        if (str[i] === replace)
        {
            temp = with_this;
        } else
        {
            temp = str[i];
        }
        str_hasil += temp;
    }
    return str_hasil;
}
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
function inIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}