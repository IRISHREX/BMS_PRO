function getHospitalExportName() {
    return (typeof SH_APP_NAME !== 'undefined' && SH_APP_NAME) ? SH_APP_NAME : 'YOUR HOSPITAL NAME';
}

function applyDataTablePrintCustomization(win, exportTitle, $tbl) {
    var hospitalName = getHospitalExportName();
    var cleanTitle = $.trim(exportTitle || '') || $.trim($('.download_label').text() || 'Report');

    // Remove any default H1
    $(win.document.body).find('h1').remove();

    // Insert Header Block matching standard hospital report design
    var headerHtml = '<div style="text-align: center; margin-bottom: 12px; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Arial, sans-serif;">' +
        '<div style="font-size: 16px; font-weight: 800; text-transform: uppercase; color: #000; letter-spacing: 0.5px; margin-bottom: 4px;">' + hospitalName + '</div>' +
        '<div style="font-size: 12px; font-weight: 700; color: #111;">' + cleanTitle + '</div>' +
    '</div>';
    $(win.document.body).prepend(headerHtml);

    // Inject exact print CSS
    var styleHtml = '<style>' +
        '@media print {' +
            '@page { size: auto; margin: 8mm 8mm 8mm 8mm; }' +
            'body { margin: 0 !important; padding: 0 !important; background: #fff !important; font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Arial, sans-serif !important; color: #000 !important; }' +
        '}' +
        'body { font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Arial, sans-serif !important; color: #000 !important; padding: 10px !important; background: #fff !important; }' +
        'table.dataTable, table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #333 !important; margin: 0 auto !important; font-size: 11px !important; font-family: inherit !important; }' +
        'table.dataTable th, table.dataTable td, table th, table td { border: 1px solid #333 !important; padding: 4px 6px !important; font-size: 11px !important; line-height: 1.3 !important; color: #000 !important; vertical-align: middle !important; }' +
        'table.dataTable th, table th { font-weight: 700 !important; background-color: #f8f9fa !important; color: #000 !important; text-align: left !important; }' +
        'th.text-end, td.text-end { text-align: right !important; }' +
        'th.text-center, td.text-center { text-align: center !important; }' +
        'th.text-start, td.text-start { text-align: left !important; }' +
        'a { color: #000 !important; text-decoration: none !important; }' +
        '.badge { border: none !important; padding: 0 !important; background: transparent !important; color: #000 !important; font-weight: normal !important; font-size: 11px !important; }' +
        '.badge i, .fa { display: none !important; }' +
    '</style>';
    $(win.document.head).append(styleHtml);

    // Read column alignments from original table
    var colAlignments = [];
    if ($tbl && $tbl.length) {
        $tbl.find('thead tr:first th:not(.noExport)').each(function() {
            if ($(this).hasClass('text-end')) {
                colAlignments.push('text-end');
            } else if ($(this).hasClass('text-center')) {
                colAlignments.push('text-center');
            } else {
                colAlignments.push('text-start');
            }
        });
    }

    if (colAlignments.length) {
        $(win.document.body).find('table thead tr:first th').each(function(idx) {
            if (colAlignments[idx]) {
                $(this).removeClass('text-start text-center text-end display').addClass(colAlignments[idx]);
            }
        });
        $(win.document.body).find('table tbody tr').each(function() {
            $(this).find('td').each(function(idx) {
                if (colAlignments[idx]) {
                    $(this).removeClass('text-start text-center text-end display').addClass(colAlignments[idx]);
                }
            });
        });
    }
}

function applyDataTablePdfCustomization(doc, exportTitle) {
    var hospitalName = getHospitalExportName();
    var cleanTitle = $.trim(exportTitle || '') || $.trim($('.download_label').text() || 'Report');

    if (doc.content && doc.content.length > 0 && doc.content[0].text) {
        doc.content[0].text = '';
    }

    doc.content.splice(0, 0,
        { text: hospitalName.toUpperCase(), fontSize: 13, bold: true, alignment: 'center', margin: [0, 0, 0, 3] },
        { text: cleanTitle, fontSize: 10, bold: true, alignment: 'center', margin: [0, 0, 0, 8] }
    );

    var tableNode = null;
    for (var i = 0; i < doc.content.length; i++) {
        if (doc.content[i].table) {
            tableNode = doc.content[i];
            break;
        }
    }
    if (tableNode) {
        tableNode.table.widths = Array(tableNode.table.body[0].length + 1).join('*').split('');
        tableNode.layout = {
            hLineWidth: function(i, node) { return 0.5; },
            vLineWidth: function(i, node) { return 0.5; },
            hLineColor: function(i, node) { return '#333333'; },
            vLineColor: function(i, node) { return '#333333'; },
            paddingLeft: function(i, node) { return 4; },
            paddingRight: function(i, node) { return 4; },
            paddingTop: function(i, node) { return 3; },
            paddingBottom: function(i, node) { return 3; }
        };
        if (tableNode.table.body.length > 0) {
            for (var c = 0; c < tableNode.table.body[0].length; c++) {
                tableNode.table.body[0][c].fillColor = '#f5f5f5';
                tableNode.table.body[0][c].bold = true;
                tableNode.table.body[0][c].fontSize = 8.5;
            }
        }
    }
    doc.defaultStyle.fontSize = 8;
    doc.pageMargins = [15, 15, 15, 15];
}

$(document).ready(function () {
    $('.example').each(function () {
        var $tbl = $(this);
        // Per-table export heading: prefer the table's own data-export-title
        // (same convention as initDatatable); fall back to the first .download_label
        // so every existing page keeps its current behaviour.
        var exportTitle = $tbl.data('exportTitle') || $('.download_label').html();
        $tbl.DataTable({
            "aaSorting": [],
            rowReorder: {
            selector: 'td:nth-child(2)'
            },
            //responsive: 'false',
            dom: '<"dt-toolbar"f<"dt-toolbar-right"lB>>rtip',
            buttons: [

                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    title: exportTitle,
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                   
                    title: exportTitle,
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    title: exportTitle,
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'PDF',
                    title: exportTitle,
                    customize: function (doc) {
                        applyDataTablePdfCustomization(doc, exportTitle);
                    },
                    exportOptions: {
                    columns: ["thead th:not(.noExport)"],
                    format: {
                        body: function(data, row, column, node) {
                            var $node = $(node);
                            var $cb = $node.find('input[type="checkbox"]');
                            if ($cb.length) {
                                return $cb.prop('checked') ? 'Yes' : 'No';
                            }
                            var $badge = $node.find('.badge');
                            if ($badge.length) {
                                return $.trim($badge.text());
                            }
                            return $.trim($node.text());
                        }
                    }
                  }
                },

                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: exportTitle,
                    customize: function ( win ) {
                        applyDataTablePrintCustomization(win, exportTitle, $tbl);
                    },
                    exportOptions: {
                    columns: ["thead th:not(.noExport)"],
                    format: {
                        body: function(data, row, column, node) {
                            var $node = $(node);
                            var $cb = $node.find('input[type="checkbox"]');
                            if ($cb.length) {
                                return $cb.prop('checked') ? 'Yes' : 'No';
                            }
                            var $badge = $node.find('.badge');
                            if ($badge.length) {
                                return $.trim($badge.text());
                            }
                            return $.trim($node.text());
                        }
                    }
                  }
                }
            ],
            "language": {
                sLengthMenu: "_MENU_"
            }
        });
    });
});


/*--dropify--*/
$(document).ready(function(){
                // Basic
                $('.filestyle').dropify();

                // Translated
                $('.dropify-fr').dropify({
                    messages: {
                        default: 'Glissez-déposez un fichier ici ou cliquez',
                        replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                        remove:  'Supprimer',
                        error:   'Désolé, le fichier trop volumineux'
                    }
                });

                // Used events
                var drEvent = $('#input-file-events').dropify();

                drEvent.on('dropify.beforeClear', function(event, element){
                    return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
                });

                drEvent.on('dropify.afterClear', function(event, element){
                    alert('File deleted');
                });

                drEvent.on('dropify.errors', function(event, element){
                    console.log('Has Errors');
                });

                var drDestroy = $('#input-file-to-destroy').dropify();
                drDestroy = drDestroy.data('filestyle')
                $('#toggleDropify').on('click', function(e){
                    e.preventDefault();
                    if (drDestroy.isDropified()) {
                        drDestroy.destroy();
                    } else {
                        drDestroy.init();
                    }
                })
            });
/*--end dropify--*/

/*--nprogress--*/
 $('body').show();
    $('.version').text(NProgress.version);
    NProgress.start();
    setTimeout(function() { NProgress.done(); $('.fade').removeClass('out'); }, 1000);
/*--nprogress--*/    
// _selector, // selector  class of table
// _url, // url is url of controller where data to be fetch
// params={}, is parameter of post method
// rm_export_btn=[], // var rm_export_btn = ["btn-pdf"] //"btn-copy","btn-excel","btn-csv","btn-pdf","btn-print" // btn-all
// pageLength=100, //per page data
// aoColumnDefs=[{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}],
// searching=true,
// aaSorting=[],
// dataSrc="data" it is array source of data


   function initDatatable(_selector,_url,params={},rm_export_btn=[],pageLength=100,aoColumnDefs=[{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}],searching=true,aaSorting=[],dataSrc="data"){
        if ($.fn.DataTable.isDataTable('.'+_selector)) { // if exist datatable it will destrory first
         $('.'+_selector).DataTable().destroy();
       }
        // Resolve the export heading once so the print customize can decide whether
        // to keep the <h1> based on the REAL title, not on .download_label existence.
        var exportTitle = $('.'+_selector).data("exportTitle");
        var table = $('.'+_selector)
    .on( 'preInit.dt', function (e, settings ) {

     var api = new $.fn.dataTable.Api( settings );
     $.each(rm_export_btn, function(key, expt_select) {
     if(expt_select === "btn-all"){
       api.buttons().remove();

     }else{
       api.buttons('.'+expt_select).remove();

     }
    });

    }).DataTable({
        // "scrollX": true,
        dom: '<"dt-toolbar"f<"dt-toolbar-right"lB>>r<t>ip',
    
         lengthMenu: [[100, -1], [100, "All"]],
       
          buttons: [
            {
                extend:    'copy',
                text:      '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                 className: "btn-copy",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'excel',
                text:      '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                     className: "btn-excel",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'csv',
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                className: "btn-csv",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'pdf',
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: "btn-pdf",
                title: exportTitle,
                customize: function (doc) {
                    applyDataTablePdfCustomization(doc, exportTitle);
                },
                exportOptions: {
                    columns: ["thead th:not(.noExport)"],
                    format: {
                        body: function(data, row, column, node) {
                            var $node = $(node);
                            var $cb = $node.find('input[type="checkbox"]');
                            if ($cb.length) {
                                return $cb.prop('checked') ? 'Yes' : 'No';
                            }
                            var $badge = $node.find('.badge');
                            if ($badge.length) {
                                return $.trim($badge.text());
                            }
                            return $.trim($node.text());
                        }
                    }
                },

            },
            {
                extend:    'print',
                text:      '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                className: "btn-print",
                title: exportTitle,
                customize: function ( win ) {
                    applyDataTablePrintCustomization(win, exportTitle, $('.' + _selector));
                },
                exportOptions: {
                    columns: ["thead th:not(.noExport)"],
                    format: {
                        body: function(data, row, column, node) {
                            var $node = $(node);
                            var $cb = $node.find('input[type="checkbox"]');
                            if ($cb.length) {
                                return $cb.prop('checked') ? 'Yes' : 'No';
                            }
                            var $badge = $node.find('.badge');
                            if ($badge.length) {
                                return $.trim($badge.text());
                            }
                            return $.trim($node.text());
                        }
                    }
                  }

            }
        ],
      
         // "scrollY":        "320px",
         
           "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> ',
             sLengthMenu: "_MENU_"
        },
        "pageLength": pageLength,
        "searching": searching,
        "aaSorting": aaSorting, // default sorting [ [0,'asc'], [1,'asc'] ]
        "autoWidth": false,
        "aoColumnDefs": aoColumnDefs, //disable sorting { "bSortable": false, "aTargets": [ 1,2 ] }
        "processing": true,
        "serverSide": true,
        
        "ajax":{
        "url": baseurl+_url,
        "dataSrc": dataSrc,
        "type": "POST",
        'data': params,
     },
        "drawCallback": function() {
            this.api().table().container().querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                var existing = bootstrap.Tooltip.getInstance(el);
                if (existing) { existing.dispose(); }
                new bootstrap.Tooltip(el);
            });
        }

    });
    return table;
    }

   function emptyDatatable(_selector,dataSrc="data"){

        $('.'+_selector).DataTable({
        "searching": false,
        "processing": true,
        "paging":   false,
        "ordering": false,
        "info":     true,
        "ajax": {
            "url": base_url+'backend/json-files/datatable_empty.json',
            "dataSrc": dataSrc
        }
    });
    }