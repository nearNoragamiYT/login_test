const TABLEPENDIENTES = $('#tbl-facturas-pendientes')
let datatable;

document.addEventListener('DOMContentLoaded', () => {
    datatablePendientes()
})

/*  Funcion para llenar y refrescar el datatable */
const datatablePendientes = () => {
    datatable = TABLEPENDIENTES.DataTable({
        ajax: {
            url: url_get_facturas_pendientes,
            type: 'POST'
        },
        columns: [
            { data: 'idCompra' },
            { data: 'NombreCompleto' },
            { data: 'Estatus Factura' },
            { data: 'FechaPago' },
            { data: 'TicketFacturacion' },
            {
                data: {},
                render: data => {
                    let component = 
                    `
                    <a href="${url_detalle_factura}/${data.idFactura}"><i id="Editar-Factura" id-Factura="${data.idFactura}" class="material-icons editar-factura">create</i></a>
                    `
                    return component;
                }
            }
        ],
        order: [[1, 'asc']]
    })
}
