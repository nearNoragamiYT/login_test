const TABLETIMBRADAS = $("#tbl-facturas-timbradas");
let datatable,
  Listado_FacturasID = [];

document.addEventListener("DOMContentLoaded", () => {
  datatableTimbradas();
});

/*  Función para llenar y refrescar el datatable */
const datatableTimbradas = () => {
  datatable = TABLETIMBRADAS.DataTable({
    ajax: {
      url: url_get_facturas_timbradas,
      type: "POST",
    },
    columns: [
      {
        data: {},
        render: (data) => {
          let component = `
                    <a class="tooltipped check_unique" data-position="top" data-delay="50">
                    <input type="checkbox" 
                    onchange="listadoIdFactura(event)"
                    id="${data.idFactura}"
                    class="checkFactura"
                    Email="${data.Email}"
                    idFactura="${data.idFactura}"/>
                    <label for="${data.idFactura}"></label>
                    </a>
                    `;
          return component;
        },
      },
      { data: "idCompra" },
      { data: "NombreCompleto" },
      { data: "FechaPago" },
      { data: "Estatus Factura" },
      { data: "FechaTimbrado" },
      {
        data: {},
        render: (data) => {
          let component = `
                    <a href="${url_detalle_factura}/${data.idFactura}" data-tooltip="Ver Factura" ><i id="Editar-Factura" id-Factura="${data.idFactura}" class="material-icons editar-factura">create</i></a>
                    <a href="${url_pdf_factura}${data.idCompra}.pdf" target="_blank" data-tooltip="Factura PDF" ><i id="pdf-Factura" class=" material-icons pdf-factura"> picture_as_pdf </i></a>
                    <a href="${url_xml_factura}${data.idCompra}.xml" target="_blank" class="xmls" value="${url_xml_factura}${data.idCompra}.xml" data-tooltip="Ver XML"> <i id="xml-factura" class=" material-icons xml-factura"> description </i></a>
                    `;
          return component;
        },
      },
    ],
    order: [[1, "asc"]],
  });
};
// cuando damos clic nos envía esl id
function listadoIdFactura(event) {
  //trae el atributo del idFactura del datatable y la almacena en idFactura
  let idFactura = parseInt(event.target.getAttribute("idFactura"));
  // almacena los valores en la primera posicion que lo encuentra
  const valor = Listado_FacturasID.indexOf(idFactura); //indexof =>si no encuentra una posición  te envia un -1 te manda el primer posicion del elemento buscado
  if (event.target.checked) {
    //Agrega un elemento a el arreglo
    Listado_FacturasID.push(idFactura);
  } else {
    //elimina el valor(idFactura) cuando lo deschekea ya que el indexof mando un -1 que significa que no lo encontro
    if (idFactura > -1) {
      Listado_FacturasID.splice(valor, 1);
    }
  }
  // console.log(Listado_FacturasID);
}
// chequea todos los elementos y guarda el id de todos
function listadoAllIdFactura() {
  show_loader_top();
  var rows = datatable.rows({ search: "applied" }).nodes();
  //chequea todos los elementos y guarda su id en un arreglo
  if ($("#btn-select-all").is(":checked")) {
    $('input[type="checkbox"]:enabled', rows).prop("checked", true);
    Listado_FacturasID = []; // se vacia el arreglo
    $.each(datatable.data("idFactura"), function (key, value) {
      Listado_FacturasID.push(value.idFactura); // agrega/inserta el id en el arreglo
    });
  } else {
    // deschekea todos los elementos y borran los id
    $('input[type="checkbox"]:enabled', rows).prop("checked", false);
    $.each(datatable.data("idFactura"), function (key, value) {
      if (value.idFactura > -1) {
        Listado_FacturasID.splice(datatable.data("idFactura"), 1);
      }
    });
  }
  hide_loader_top();
  // console.log(Listado_FacturasID);
}
// boton de envio de mails
$("#btn-email").click(() => {
  show_loader_top();
  let params = {};
  params.idFactura = Listado_FacturasID;
  // console.log(params.idFactura);
  sendData(params);
});
//envio de datos
function sendData(data) {
  show_loader_wrapper();
  // console.log(data);
  $.ajax({
    type: "post",
    url: url_envio_idEmail,
    dataType: "json",
    data: data,
    success: function (response) {
      // console.log(response);
      // comprueba el status para que cuando los correos sean verdaderos  no muestre el modal
      if (response["status"] == false) {
        //abre el modal cuando se envian los correos y dismissible hace que el modal al abrir no pueda cerrar si le dan clic afuera
        $("#modal1").modal({ dismissible: false }).modal("open");

        message = " ";
        // genero los elementos para la construcción  de la tabla
        let table = document.createElement("table");
        let thead = document.createElement("thead");
        let tbody = document.createElement("tbody");
        // Crear y agregar datos a la primera fila de la tabla

        let titulo_table = document.createElement("tr");
        titulo_table.setAttribute("id", "titulo"); // agregamos un atributo a el elemento
        let titulo_1 = document.createElement("th");
        titulo_1.innerHTML = "Correo";
        let titulo_2 = document.createElement("th");
        titulo_2.innerHTML = "factura";

        titulo_table.appendChild(titulo_1);
        titulo_table.appendChild(titulo_2);
        thead.appendChild(titulo_table);
        // Agregar toda la tabla a la etiqueta del cuerpo
        document.getElementById("data").appendChild(table);
        // recorre un arreglo para mandar mensaje a todos los correos y despues los manda a un modal con los correos que no se mandaron
        $.each(response["datos"], function (indexw, valuew) {
          //guarda los valores de las posiciones
          message = `${valuew[indexw][0]}`;
          facturaID = `${valuew[indexw][1]}`;
          factura = table.appendChild(thead);
          table.appendChild(tbody);

          // Crear y agregar datos a la segunda fila de la tabla
          let datos_tabla = document.createElement("tr");
          datos_tabla.setAttribute("id", "datos");
          let correos_tabla_mail = document.createElement("td");
          correos_tabla_mail.innerHTML = message;
          let correos_tabla_factura = document.createElement("td");
          correos_tabla_factura.innerHTML = facturaID;

          datos_tabla.appendChild(correos_tabla_mail);
          datos_tabla.appendChild(correos_tabla_factura);
          tbody.appendChild(datos_tabla);
        });
      }

      show_toast("success", "Id mail se enviaron con éxito");
      hide_loader_wrapper();
    },
    error: function (request, status, error) {
      show_modal_error(request.responseText);
      hide_loader_wrapper();
    },
  });
}
//cierra el modal y lo vacía
$(".close-modal").on("click", function () {
  $("#data").empty();
  $("#modal1").modal("close");
});
