// inicializa javaScrip
// colores de la base de datos
var colorHeaderRGB = configuracion.colorHeader;
var colorPortalRGB = configuracion.ColorPortal;
// colores actualizados
var colorH = configuracion.colorHeader;
var colorP = configuracion.ColorPortal;

document.addEventListener("DOMContentLoaded", () => {
  convertirRgbHex();
});
// guarda los campos para despues enviarlos
$("#btnConfiguracion").click(() => {
  // selecciona la posicion del checked
  let idTipoUsuario = $("#idTipoUsuario").prop("checked") ? 2 : 1; //produccion / prueba
  let eventoUrl = $("#EventoUrl").val();
  let params = {};
  params.idTipoUsuario = idTipoUsuario;
  params.eventoUrl = eventoUrl;
  params.colorHeader = colorH;
  params.colorPortal = colorP;

  sendData(params);
});
// convertir de hexadecimal a RGB
$("#colorHeader").on("change", function () {
  //toma el valor del color en hexadecimal
  let colorHeader = $("#colorHeader").val();
  //se separan de dos en dos los colores hexadecimal para pasarlos a rgb
  let rojoHeader = parseInt(colorHeader[1] + colorHeader[2], 16);
  let verdeHeader = parseInt(colorHeader[3] + colorHeader[4], 16);
  let azulHeader = parseInt(colorHeader[5] + colorHeader[6], 16);
  let ColoresRGBHeader = `rgb(${rojoHeader}, ${verdeHeader}, ${azulHeader})`;
  $("#tituloColorHeader").text(ColoresRGBHeader);
  colorH = ColoresRGBHeader;
});

// convertir de hexadecimal a RGB
$("#ColorPortal").on("change", function () {
  let colorPortal = $("#ColorPortal").val();
  //se separan de dos en dos los colores hexadecimal para pasarlos a rgb
  let rojoPortal = parseInt(colorPortal[1] + colorPortal[2], 16);
  let verPortal = parseInt(colorPortal[3] + colorPortal[4], 16);
  let azulPortal = parseInt(colorPortal[5] + colorPortal[6], 16);
  let ColoresRGBPortal = `rgb(${rojoPortal}, ${verPortal}, ${azulPortal})`;
  $("#tituloColorPortal").text(ColoresRGBPortal); //muestra el color seleccionado
  colorP = ColoresRGBPortal;
});
// convierte rgb a hexadecimal
function convertirRgbHex() {
  colorHeaderRGB = colorHeaderRGB.slice(4);
  colorHeaderRGB = colorHeaderRGB.replace(")", "");
  colorHeaderRGB = colorHeaderRGB.trim();
  colorHeaderRGB = colorHeaderRGB.split(" ");

  colorPortalRGB = colorPortalRGB.slice(4);
  colorPortalRGB = colorPortalRGB.replace(")", "");
  colorPortalRGB = colorPortalRGB.trim();
  colorPortalRGB = colorPortalRGB.split(" ");

  let redHeader = parseInt(colorHeaderRGB[0]);
  let greenHeader = parseInt(colorHeaderRGB[1]);
  let blueHeader = parseInt(colorHeaderRGB[2]);
  let hexadecimalHeader = ConvertRGBtoHex(redHeader, greenHeader, blueHeader);

  let redPortal = parseInt(colorPortalRGB[0]);
  let greenPortal = parseInt(colorPortalRGB[1]);
  let bluePortal = parseInt(colorPortalRGB[2]);
  let hexadecimalPortal = ConvertRGBtoHex(redPortal, greenPortal, bluePortal);

  $("#colorHeader").val(hexadecimalHeader);
  $("#ColorPortal").val(hexadecimalPortal);
}

function ColorToHex(colorH) {
  var hexadecimal = colorH.toString(16);
  return hexadecimal.length == 1 ? "0" + hexadecimal : hexadecimal;
}

function ConvertRGBtoHex(red, green, blue) {
  return "#" + ColorToHex(red) + ColorToHex(green) + ColorToHex(blue);
}
// //switch prueba o produccion
// /* $("#idTipoUsuario").on("change", function () {
//   let idTipoUsuario = $("#idTipoUsuario").val();
//   let estatusTipoUsuario = $("#idTipoUsuario").prop("checked") ? 2 : 1;
//   console.log(estatusTipoUsuario , idTipoUsuario)
// }); */

function sendData(data) {
  show_loader_wrapper();
  // console.log(data);
  $.ajax({
    type: "post",
    url: url_insertar_configuracion,
    dataType: "json",
    data: data,
    success: function (response) {
      if (!response["status"]) {
        hide_loader_wrapper();
      }
      show_toast("success", "Configuracion Guardada con éxito");
      submitFormFilesEmpresa(configuracion.idConfiguracion);
      hide_loader_wrapper();
    },
    error: function (request, status, error) {
      show_modal_error(request.responseText);
      hide_loader_wrapper();
    },
  });
}

var files_empresa = {};

$("#empresa-configuracion-form input[type=file]").on(
  "change",
  prepareUploadFiles
);
$("#empresa-configuracion-form :file").on("change", previewImage);

function previewImage() {
  var oFReader = new FileReader();
  var fileinput = $(this);
  oFReader.readAsDataURL(
    document.getElementById(fileinput.attr("id")).files[0]
  );
  oFReader.onload = function (oFREvent) {
    var dataURL = oFREvent.target.result;
    var mimeType = dataURL.split(",")[0].split(":")[1].split(";")[0];

    document.getElementById(fileinput.attr("rel-preview")).src = "";
    if (mimeType.match("image.*")) {
      document.getElementById(fileinput.attr("rel-preview")).src = dataURL;
    } else {
      console.log("ooppsss");
      // document.getElementById(fileinput.attr('rel-preview')).src = src_fail;
    }
  };
}

function prepareUploadFiles(event) {
  if (isset(event.target.files[0])) {
    files_empresa[configuracion.idConfiguracion] = event.target.files[0];
  } else {
    delete files_empresa[configuracion.idConfiguracion];
  }
}

function submitFormFilesEmpresa(id) {
  if (files_empresa == null) {
    return;
  }
  var data = new FormData();
  data.append("idConfiguracion", id);
  for (const [key, value] of Object.entries(files_empresa)) {
    data.append(key, value);
  }
  if (files_empresa[2] != null) {
    fetch(url_upload_files, {
      method: "POST",
      body: data,
    })
      .then((response) => response.text())
      .then((body) => {
        try {
          const json = JSON.parse(body);
          if (!json.status) show_alert("danger", json.data);
        } catch (err) {
          throw new Error(body);
        }
      })
      .catch((error) => show_modal_error(error));
  }
}
