const formDatosgn = document.querySelectorAll("#formDtGenerales");
const formDatosfs = document.querySelectorAll("#formDtFiscales");
const btnVisualizaPDF = document.querySelector("#btn-visualizar");
const frameVisualizaPDF = document.querySelector("#frame-factura");
const mdlVisualizFactura = document.querySelector("#modal-factura");
const mdlEnviarFactura = document.querySelector("#modalEnviar");
const btnCerrarFactura = document.querySelectorAll(".close-modal");
const btnEnviarFactura = document.querySelector("#btn-enviar-factura");
const btnEnviarCorreo = document.querySelector(".btn-enviar-correo");
const frmEnviarFactura = document.querySelector("#frm-enviarFactura");
var btnGenerarFactura = document.getElementById("enviar-cancelacion");
let idInformacion = $("#UUIDCambio").val();

/**
 * funcion inicial javascript.
 */
document.addEventListener("DOMContentLoaded", function () {
  disableForm(formDatosgn);
  disableForm(formDatosfs);
  cerrarModal();
});

////////////////Funciones////////////////////////////
/**
 * funcion para deshabilitar inputs formularios.
 * @params {form}
 * @return
 */
function disableForm(form) {
  Object.entries(form[0]).map((value) => {
    value[1].setAttribute("readonly", true);
  });
}

/**
 * funcion para crear la opacidad al abrir un modal.
 * @params
 * @return
 */
function createdivOpacityBody() {
  divModal = document.querySelector("#divModalOp");
  divModal.style.display = "block";
  divModal.style.backgroundColor = "#ccc";
  divModal.style.opacity = "0.5";
  divModal.style.zIndex = "1200";
  divModal.style.width = "100%";
  divModal.style.height = "100%";
  divModal.style.position = "absolute";
}

function createDataForm(form) {
  var jsonObj = {};
  Object.entries(form).map((value, index) => {
    let campo = value[1].name;
    let valor = value[1].value;
    jsonObj[campo] = valor;
  });
  return jsonObj;
}

////////////////////////////Eventos, arrow functions //////////////////////////
btnVisualizaPDF.addEventListener("click", () => {
  createdivOpacityBody();
  mdlVisualizFactura.showModal();
});

btnEnviarFactura.addEventListener("click", () => {
  createdivOpacityBody();
  mdlEnviarFactura.showModal();
});

btnEnviarCorreo.addEventListener("click", () => {
  show_loader_wrapper();
  mdlEnviarFactura.close();
  divModal.style.display = "none";
  const form = createDataForm(frmEnviarFactura);
  fetch(get_factura_pendiente, {
    method: "POST",
    body: JSON.stringify(form),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((json) => {
      console.log(json);
      if (json.status) {
        show_toast("success", json.message);
        hide_loader_wrapper();
      } else {
        show_toast("Warning", json.message);
        hide_loader_wrapper();
      }
    });
});

const cerrarModal = () => {
  Object.entries(btnCerrarFactura).map((value) => {
    value[1].addEventListener("click", () => {
      mdlEnviarFactura.close();
      mdlVisualizFactura.close();
      divModal.style.display = "none";
    });
  });
};

/* cancelacion Modal*/

$("#btnCancelar").on("click", function () {
  $("#Canelacion").modal({ dismissible: false }).modal("open");
});
//cierra el modal y lo vacía
$(".close-modal-cancelacion").on("click", function () {
  // $("#data").empty();
  $("#Canelacion").modal("close");
});

function listadoIdFactura(event) {
  let idMotivo = parseInt(event.target.value);
  let folioSustitucion;
  folioSustitucion = idInformacion;
  if (idMotivo == 1) {
    $("#UUIDCambio").prop("readonly", false);
    $("#UUIDCambio").val(folioSustitucion);
  } else {
    $("#UUIDCambio").prop("readonly", true);
    $("#UUIDCambio").val("");
  }
}
/* final de la cancelacion*/
// $(".enviar-modal-cancelacion").on("click", function () {
// console.log('aqui estuve')
//   /*4*/ let FolioSustitucion = $("#UUIDCambio").val();
//   /*5*/ let idClaveMotivo = $("#motivo").push("idMC");
// });
/* */
btnGenerarFactura.addEventListener("click", function (e) {
  e.preventDefault();

  /*1*/ let RFC = $("#RFC").val();
  /*2*/ let UUID = $("#UUID").val();
  /*3*/ let ClaveMotivo = $("#motivo").val();
  /*4*/ let FolioSustitucion = $("#UUIDCambio").val();
  /*5*/ let idConfiguracionPortal = $("#idcofiguracion").val();
  let idFactura = $("#idFact").val();
  let UUIDCompra = $("#UUIDCompra").val();
  // let idConfiguracion = $("#idcofiguracion").val();
  /*7*/

  if (ClaveMotivo == 01) {
    console.log("llego->01");

    if (RFC != "" && UUID != "" && ClaveMotivo != "") {
      generarFactura(
        RFC,
        UUID,
        ClaveMotivo,
        FolioSustitucion,
        idFactura,
        idConfiguracionPortal,
        UUIDCompra
      );
    } else {
      show_notification("erro", "Ocurrio un problema");
    }
  } else {
    console.log("llego->diferente de 01");

    if (RFC != "" && UUID != "" && ClaveMotivo != "") {
      generarFactura(
        RFC,
        UUID,
        ClaveMotivo,
        FolioSustitucion,
        idFactura,
        idConfiguracionPortal,
        UUIDCompra
      );
    } else {
      show_notification("erro", "Ocurrio un problema");
    }
  }
});
function generarFactura(
  rfc,
  uuid,
  motivo,
  folioSustitucion,
  idFactura,
  idConfiguracionPortal,
  UUIDCompra
) {
  let data = new URLSearchParams({
    RFC: rfc,
    UUID: uuid,
    ClaveMotivo: motivo,
    FolioSustitucion: folioSustitucion,
    idFactura: idFactura,
    idConfiguracionPortal: idConfiguracionPortal,
    UUIDCompra: UUIDCompra,
  });
  fetch(url_CancelarF, {
    method: "POST",
    body: data,
  })
    .then((response) => response.json())
    .then((json) => {
      if (json.status) {
        show_toast("success", "Se Cancelo la factura con éxito");
      } else {
        show_toast("warning", json.message);
      }
    });
}

$("#ConsultaUUID").on("click", function () {
  // console.log("llega hasta aqui")
  let UUID = $("#UUID").val();
console.log(UUID)
});

/* */
