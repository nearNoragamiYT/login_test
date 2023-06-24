var formsAssoc = {}, exTable = '', columsSearched = {}, clspan = 0, orderSections = Object.keys(sections).length + 1, picker = "", imgDrop = "", pdfDrop = "", array_exhibitors = [], array_selected = [], exhibitors;
var array_status = {
    "0": {
        "icon": "fa-file",
        "label": general_text['sas_pendiente']
    },
    "1": {
        "icon": "fa-file-text",
        "label": section_text['sas_completo']
    },
    "2": {
        "icon": "fa-file-excel-o",
        "label": section_text['sas_sinInteres']
    }
};
var array_type = {
    "1": {
        "icon": "fa-file-text-o",
        "label": "Smart"
    },
    "2": {
        "icon": "fa-file-pdf-o",
        "label": "PDF"
    },
    "3": {
        "icon": "fa-link",
        "label": "Link"
    },
    "4": {
        "icon": "fa-external-link",
        "label": "Link"
    }
};
var array_obligarory = {
    "0": {
        "icon": "fa-square-o",
        "label": section_text.sas_formaNoObligatoria
    },
    "1": {
        "icon": "fa-check-square-o",
        "label": section_text.sas_formaObligatoria
    }
};
var array_close = {
    "0": {
        "icon": "fa-unlock-alt",
        "label": section_text.sas_formaAbierta
    },
    "1": {
        "icon": "fa-lock",
        "label": section_text.sas_formaCerrada
    }
};
var array_payment = {
    "0": section_text['sas_sinDefinir'],
    "1": section_text['sas_pagado'],
    "2": section_text['sas_pagoRevisado'],
    "3": section_text['sas_pagoDeclinado'],
    "4": general_text['sas_pendiente']
};
var array_lang = {
    0: section_text['sas_formaNoLlena'],
    "es": section_text['sas_idiomaES'],
    "en": section_text['sas_idiomaEN']
};
$(init);
function init() {
    constructFormsTable(); //construye la tabla
    formFilters(); //inicializa los filtros
    buttonActions(); //ejecuta todas las funciones de los elementos que ejecutan una accion al hacer click sobre el elemento
    initDropzone();
    hide_loader_wrapper();
}
/**
 * Construye la tabla
 */
function constructFormsTable() {
    clspan = $('#cover-forms-table thead tr th').length;
    var tr = "", td = "", tb = "", spn = "", sec = null, sec1 = null, div = "", img = "", i = "";
    $.each(forms, function (index, form) {
        formsAssoc[form['idForma']] = form;
        //----  Si es que es sección nueva hace un colspan con el nombre de la sección   ----//
        if (form['idSeccionFormatos'] !== sec || !sec) {
            sec = form['idSeccionFormatos'];
            tb = constructSectionTable(sections[form['idSeccionFormatos']]);
        }
        //--- construlle el renglon de la forma ---//
        tr = buildFormTable(form);
        tb.appendChild(tr);
        if (form['idSeccionFormatos'] !== sec1 || !sec1) {
            sec1 = form['idSeccionFormatos'];
            $("#cover-forms-table").append(tb);
        }
    });
    $.each(sections, function (id, section) {
        if ($('#tbody-' + section['idSeccionFormatos']).length === 0) {
            var tb = constructSectionTable(section);
            $("#cover-forms-table").append(tb);
        }
    });
    ajaxOrder();
    $('#cover-forms-table').floatThead({
        position: "fixed",
        zIndex: 5,
        top: 64
    });
}
/**
 * ejecuta las acciones iniciales para los elementos del DOM
 */
function buttonActions() {
    //$('select').material_select();
    $('.tooltipped').each(function (i, ele) {
        $(ele).tooltip({delay: 50, position: 'top'});
    });
    //--- Botón para editar el mail ---//
    $("#edit-email").click(function () {
        var a = document.getElementById('correo_ifr');
        if (section_text['sas_emailFormaPendiente'] === '') {
            section_text['sas_emailFormaPendiente'] = ' ';
        }
        a.contentDocument.body.innerHTML = section_text['sas_emailFormaPendiente'];
        $('#ModalEditEmail').modal("open");
    });
    //--- Botón para enviar los mails a los expositores ---//
    $("#send-email").on('click', function () {
        sendEmailPendingForm(exhibitors);
    });
    //--- boton para bloquear las empresaforma de un expositor ---//
    $(document).on('click', '.emfo-lock', function () {
        show_loader_wrapper();
        lockUnlockEMFO($(this).attr('data-id'), $(this).attr('data-lock'));
    });
    //--- Boton para mandar el ajax de la fecha límite ---//
    $('#send-deadline').on('click', function () {
        updateDeadline($(this).attr("data-id-form"), $(this).attr('data-deadline'));
    });
    //--- botón para quitar la fecha límite ---//
    $('#delete-deadline').on('click', function () {
        updateDeadline($(this).attr("data-id-form"), null);
    });
    //--- boton para cambiar status(bloquear y desbloquear forma) así como para haacerla obligatoria o no ---//
    $(document).on("click", '.status', function () {
        changeStatusForm($(this).attr('data-form-id'), $(this).attr('data-status'), $(this).attr('data-type'));
    });
    //--- Boton para subir el pdf ---//
    $(document).on('click', '.fa-file-pdf-o', function () {
        PDFActions($(this));
    });
    //--- Boton para actualizar el link ---//
    $('.fa-external-link').on('click', function () {
        linkActions($(this));
    });
    $('#update-link').on('click', function () {
        $('#form-link').submit();
    });
    //--- Botones para mostrar las gráficas ---//
    $(document).on('click', '.fa-line-chart', function () {
        chartsActions($(this));
    });
    //--- botón para actualizar datos de la seccion ---//
    $(document).on('click', '.edit-section', function () {
        editSection($(this).attr('data-section-id'));
    });
    //--- boton para mostrar el modal de una nueva sección ---//
    $('#new-section').on('click', function () {
        newSectionActions($(this));
    });
    validateSection(url_save_section);
    $('#add-section').on('click', function () {
        $('#form-add-section').submit();
    });
    //--- botón para eliminar una seccion que haya creado el comité ---//
    $('#btn-delete-section').on('click', function () {
        $('#delete-info').attr({'data-id': $(this).attr('data-id'), 'data-type': "section"});
        $('#info-delete').html(section_text['sas_eliminarSeccion'].replace('%section%', sections[$(this).attr('data-id')]['Nombre' + lang.toUpperCase()]));
        $('#modal-confirm-delete').modal("open").modal({dismissible: false});
    });
    //--- botón para agregar una nueva forma ---//
    $('#new-form').on('click', function () {
        newFormActions();
    });
    $('#add-form').on('click', function () {
        $('#form-add').submit();
    });
    validateNewForm();
    //--- botón para eliminar una forma que haya creado el comité ---//
    $('#btn-delete-form').on('click', function () {
        $('#delete-info').attr({'data-id': $(this).attr('data-id'), 'data-type': "form"});
        $('#info-delete').html(section_text['sas_eliminarForma'].replace('%form%', formsAssoc[$(this).attr('data-id')]['NombreForma' + lang.toUpperCase()]));
        $('#modal-confirm-delete').modal("open").modal({dismissible: false});
    });
    //--- botón para eliminar la info de la forma como de la seccion---//
    $('#delete-info').on('click', function () {
        $('#modal-confirm-delete').modal("close");
        if ($(this).attr('data-type') === 'form') {
            deleteForm($(this).attr("data-id"));
        } else if ($(this).attr('data-type') === 'section') {
            if ($(".row-" + $(this).attr('data-id')).length > 0 && $(".row-" + $(this).attr('data-id')).is(':visible')) {
                show_toast('info', section_text.sas_debeEliminarFormas);
            } else {
                deleteSection($(this).attr("data-id"));
            }
        }
    });
    //--- Redirige al editor de textos de la forma ---//
    $(document).on('click', '.fa-file-text-o', function () {
        show_loader_wrapper();
        window.location = $(this).attr('data-link');
    });
    //--- botón para editar la forma ---//
    $(document).on('click', '.edit-form', function () {
        editFormaAction($(this));
    });
    $('#update-form').on('click', function () {
        $('#form-update').submit();
    });
    validateFormEdit();
    //--- botón para eliminar una forma que haya creado el comité ---//
    $('#btn-delete-form').on('click', function () {
        $('#delete-info').attr({'data-id': $(this).attr('data-id'), 'data-type': "form"});
        $('#info-delete').html(section_text['sas_eliminarForma'].replace('%form%', formsAssoc[$(this).attr('data-id')]['NombreForma' + lang.toUpperCase()]));
        $('#modal-confirm-delete').modal("open").modal({dismissible: false});
    });
    //--- botón para eliminar la info de la forma como de la seccion---//
    $('#delete-info').on('click', function () {
        $('#modal-confirm-delete').modal("close");
        if ($(this).attr('data-type') === 'form') {
            deleteForm($(this).attr("data-id"));
        } else if ($(this).attr('data-type') === 'section') {
            if ($(".row-" + $(this).attr('data-id')).length > 0 && $(".row-" + $(this).attr('data-id')).is(':visible')) {
                show_toast('info', section_text.sas_debeEliminarFormas);
            } else {
                deleteSection($(this).attr("data-id"));
            }
        }
    });
    //--- Botón para regresar a ver el indice de formatos ---//
    $(".back-to-forms-list").click(function () {
        formsList("show");
        array_selected = [];
        $("#send-email-all").prop('disabled', true);
    });
}
/**
 * inicia los filtros para busquedas en la tabla
 */
function formFilters() {
    $('#filter-option').on('change', function () {
        $("#filter-text").val('');
        var searchTerm = $(this).val();
        $('.toast-alert').remove();
        if (searchTerm !== "" && searchTerm !== null) {
            var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
            $("#cover-forms-table tbody tr").not("." + searchSplit).each(function (e) {
                $(this).hide();
            });
            $("#cover-forms-table tbody tr." + searchSplit).each(function (e) {
                $(this).show(200);
            });
            $("#cover-forms-table tbody tr.section").each(function (e) {
                $(this).show(200);
            });
            var jobCount = $('#cover-forms-table tbody tr').not('.section').is(':visible');
            if (!jobCount) {
                show_toast('warning', general_text.sas_sinResultados);
            }
        } else {
            $("#cover-forms-table tbody tr").each(function (e) {
                if ($(this).hasClass('no-result')) {
                    $(this).parent().hide();
                }
                $(this).show(200);
            });
        }
    });
    //--- limpia el buscador ---//
    $('#close-search').on('click', function () {
        $('input[type="search"]').val("");
        $(this).prev().removeClass('active');
        $('.no-result').hide();
        $("#cover-forms-table tbody tr").each(function (e) {
            $(this).show(200);
        });
    });
    $("#filter-text").keyup(function () {
        var searchTerm = $(this).val();
        var opt = $('#filter-option').val();
        $('.alert-toast').remove();
        if (searchTerm !== "" && searchTerm !== null) {
            var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
            $.extend($.expr[':'], {'containsi': function (elem, i, match, array) {
                    return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                }
            });
            if (opt !== "" && opt !== null) {//muestra las letras que esten con el filtro seleccionado
                $("#cover-forms-table tbody tr." + opt).not(":containsi('" + searchSplit + "')").each(function (e) {
                    $(this).hide(100);
                });
                $("#cover-forms-table tbody tr." + opt + ":containsi('" + searchSplit + "'), .section").each(function (e) {
                    $(this).show(200);
                });
                var jobCount = $('#cover-forms-table tbody tr.' + opt).not('.section').is(":visible");
                if (!jobCount) {
                    show_toast('warning', general_text.sas_sinResultados);
                }
            } else {//busca en toda la tabla
                $("#cover-forms-table tbody tr").not(":containsi('" + searchSplit + "'), .section").each(function (e) {
                    $(this).hide(100);
                });
                $("#cover-forms-table tbody tr:containsi('" + searchSplit + "'), .section").each(function (e) {
                    $(this).show(200);
                });
                var jobCount = $('#cover-forms-table tbody tr').not('.section').is(':visible');
                if (!jobCount) {
                    show_toast('warning', general_text.sas_sinResultados);
                }
            }
        } else {
            if (opt !== "" && opt !== null) {
                $("#cover-forms-table tbody tr." + opt).each(function (e) {
                    $(this).show(200);
                });
            } else {
                $("#cover-forms-table tbody tr").each(function (e) {
                    $(this).show(200);
                });
            }
        }
    });
}
/**
 * Inicia el plugin dropzone
 */
function initDropzone(id) {
    imgDrop = $('#update-image').dropzone({
        url: url_save_image,
        paramName: "modal-imagen",
        maxFilesize: 2, //MB
        method: "post",
        uploadMultiple: false,
        previewsContainer: false,
        dictFileTooBig: section_text.sas_imagenExcedioPeso,
        dictResponseError: section_text.sas_errorInternoImagen,
        dictInvalidFileType: section_text.sas_errorTipoImagen,
        acceptedFiles: ".jpg, .png, .jpeg",
        accept: function (file, done) {
            if (file.status !== "error") {
                $('.modal-progress').fadeIn();
                done();
            }
        },
        sending: function (file, xhr, formData) {
            formData.append('idSeccionFormatos', $('#idSeccionFormatos').val());
        },
        error: function (file, error) {
            $('.modal-progress').fadeOut();
            show_modal_error(error);
        },
        complete: function (file) {
            $('.modal-progress').fadeOut();
            try {
                var res = JSON.parse(file.xhr.response);
                if (res['status']) {
                    var preview = document.querySelector('img[id="section-img"]');
                    var reader = new FileReader();
                    reader.addEventListener("load", function () {
                        preview.src = reader.result;
                    }, false);
                    if (file) {
                        reader.readAsDataURL(file);
                    }
                }
            } catch (e) {
                show_modal_error(general_text.sas_errorSubirArchivo + "<br>" + file.xhr.responseText);
            }
        }
    });
    pdfDrop = $('#update-pdf').dropzone({
        url: url_save_pdf,
        paramName: "modal-pdf",
        maxFilesize: 10, //MB
        method: "post",
        uploadMultiple: false,
        previewsContainer: false,
        dictFileTooBig: section_text.sas_pdfExcedioPeso,
        dictResponseError: section_text.sas_errorInternoPDF,
        dictInvalidFileType: section_text.sas_errorTipoPDF,
        acceptedFiles: ".pdf, .xls, .xlsx, .ppt, .pps, .ppsx",
        accept: function (file, done) {
            if (file.status !== "error") {
                $('.modal-progress').fadeIn();
                done();
            }
        },
        sending: function (file, xhr, formData) {
            var id = $(this.element).attr('data-id');
            var idiom = $(this.element).attr('data-lang');
            formData.append('idForma', id);
            formData.append('FechaActualizacion', getDateUp());
            formData.append('idioma', idiom);
            var nombre = (formsAssoc[id]['NombreFormaES'] == formsAssoc[id]['NombreFormaEN']) ? formsAssoc[id]['NombreForma' + idiom] + idiom : formsAssoc[id]['NombreForma' + idiom];
            formData.append('NombreArchivo', nombre);
        },
        error: function (file, error) {
            $('.modal-progress').fadeOut();
            show_modal_error(error);
        },
        complete: function (file) {
            $('.modal-progress').fadeOut();
            try {
                var res = JSON.parse(file.xhr.response);
                if (res['status']) {
                    var rute = res.data['url'].split(".");
                    if (rute.slice(-1)[0] === "pdf") {
                        $('#show-pdf').show();
                        $('#download-power').hide();
                        $('#download-excel').hide();
                        var preview = document.querySelector('#show-pdf');
                        preview.src = viewer + "../../../" + res.data['url'];
                    } else if (rute.slice(-1)[0] === "xls" || rute.slice(-1)[0] === "xlsx") {
                        $('#show-pdf').hide();
                        $('#download-power').hide();
                        $('#download-excel').attr('href', url_public + res.data['url']).show();
                    } else {
                        $('#show-pdf').hide();
                        $('#download-excel').hide();
                        $('#download-power').attr('href', url_public + res.data['url']).show();
                    }
                    formsAssoc[res.data['idForma']]['Link' + res.data['idioma']] = res.data['url'];
                    formsAssoc[res.data['idForma']]['FechaActualizacion' + res.data['idioma']] = res.data['FechaActualizacion'];
                    $('#' + res.data['idForma'] + '-' + res.data['idioma']).attr('data-tooltip', section_text.sas_ultimaActulizacion + " " + res.data['FechaActualizacion']).removeClass('gray-text').addClass('red-text');
                    $('#last-update-pdf').text(section_text.sas_fechaActualizacion + " " + res.data['FechaActualizacion']);
                }
            } catch (e) {
                show_modal_error(general_text.sas_errorSubirArchivo + "<br>" + file.xhr.responseText);
            }
        }
    });
}
