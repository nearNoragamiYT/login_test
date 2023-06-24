const TABLECANCELADA = $('#tbl-facturas-canceladas')
let datatable;

document.addEventListener('DOMContentLoaded', () => {
    datatableCanceladas()
})

/*  Funcion para llenar y refrescar el datatable */
const datatableCanceladas = () => {
    datatable = TABLECANCELADA.DataTable({
        ajax: {
            url: url_get_facturas_canceladas,
            type: 'POST'
        },
        columns: [
            { data: 'idCompra' },
            { data: 'NombreCompleto' },
            { data: 'FechaPago' },
            { data: 'Estatus Factura' },
            { data: 'FechaTimbrado' },
            {
                data: {},
                render: data => {
                    let component = 
                    `<a href="${url_detalle_factura}/${data.idFactura}"><i id="Editar-Factura" id-Factura="${data.idFactura}" class="material-icons editar-factura">create</i></a>`
                    return component;
                }
            }
        ],
        order: [[1, 'asc']]
    })
}
