<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <?php $this->load->view('admin/report/_finance'); ?>
            <div class="card-header ptbnull"></div>
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title"><?php echo $this->lang->line('department_wise_transaction_report'); ?></h3>
                <button type="button" class="btn btn-primary btn-sm ms-auto" id="btn_print_dwtr">
                    <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                </button>
            </div>
            <div class="card-body pb-0">
                <!-- 3 KPI Cards above filters -->
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <div class="kpi">
                            <div class="ic green"><i class="fa fa-money"></i></div>
                            <div>
                                <div class="val" id="kpi_total_paid"><?php echo $currency_symbol; ?> 0.00</div>
                                <div class="lbl"><?php echo $this->lang->line('total_paid') ?: 'Total Paid'; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="kpi">
                            <div class="ic red"><i class="fa fa-undo"></i></div>
                            <div>
                                <div class="val" id="kpi_total_refund"><?php echo $currency_symbol; ?> 0.00</div>
                                <div class="lbl"><?php echo ($this->lang->line('total_refund') == 'Total Refund') ? 'Total Refunded' : ($this->lang->line('total_refund') ?: 'Total Refunded'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="kpi">
                            <div class="ic teal"><i class="fa fa-calculator"></i></div>
                            <div>
                                <div class="val" id="kpi_net_amount"><?php echo $currency_symbol; ?> 0.00</div>
                                <div class="lbl"><?php echo $this->lang->line('overall_net_amount') ?: 'Overall Net Amount'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="form_dwtr" action="" method="post">
                    <div class="row">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('search_type'); ?></label><small class="req"> *</small>
                                <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($searchlist as $key => $search) { ?>
                                        <option value="<?php echo $key ?>" <?php if ((isset($search_type)) && ($search_type == $key)) { echo "selected"; } elseif (!isset($search_type) && $key == 'this_year') { echo "selected"; } ?>><?php echo $search ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="error_search_type"><?php echo form_error('search_type'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none" id="fromdate">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_from'); ?></label>
                                <input id="date_from" name="date_from" type="text" class="form-control date" value="<?php echo set_value('date_from', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger" id="error_date_from"><?php echo form_error('date_from'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none" id="todate">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_to'); ?></label>
                                <input id="date_to" name="date_to" type="text" class="form-control date" value="<?php echo set_value('date_to', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger" id="error_date_to"><?php echo form_error('date_to'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('department'); ?></label>
                                <select class="form-control" name="department" id="department">
                                    <?php foreach ($departments as $dept_key => $dept_val) { ?>
                                        <option value="<?php echo $dept_key; ?>"><?php echo $dept_val; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-auto d-flex align-items-end ps-md-3 pe-md-3">
                            <div class="mb-3">
                                <button type="submit" name="search" value="search_filter" class="btn btn-primary d-inline-flex align-items-center gap-1 py-2">
                                    <i class="fa fa-search"></i>
                                    <?php echo $this->lang->line('search'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
<style>
    #dept_trans_table td:nth-child(1),
    #dept_trans_table th:nth-child(1),
    #dept_trans_table td:nth-child(2),
    #dept_trans_table th:nth-child(2),
    #dept_trans_table td:nth-child(3),
    #dept_trans_table th:nth-child(3),
    #dept_trans_table td:nth-child(4),
    #dept_trans_table th:nth-child(4),
    #dept_trans_table td:nth-child(5),
    #dept_trans_table th:nth-child(5),
    #dept_trans_table td:nth-child(6),
    #dept_trans_table th:nth-child(6),
    #dept_trans_table td:nth-child(7),
    #dept_trans_table th:nth-child(7) {
        white-space: nowrap !important;
    }
</style>
            </div>
            <div class="card-body table-responsive pt-0">
                <div class="download_label"><?php echo $this->lang->line('department_wise_transaction_report'); ?></div>
                <table class="table table-striped table-bordered table-hover allajaxlist" id="dept_trans_table" data-export-title="<?php echo $this->lang->line('department_wise_transaction_report'); ?>">
                    <thead>
                        <tr>
                            <th class="text-nowrap"><?php echo $this->lang->line('date'); ?></th>
                            <th><?php echo $this->lang->line('department'); ?></th>
                            <th class="text-nowrap"><?php echo $this->lang->line('total_transaction'); ?></th>
                            <th class="text-end text-nowrap"><?php echo $this->lang->line('paid_amount') ?: 'Paid Amount'; ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                            <th class="text-end text-nowrap"><?php echo $this->lang->line('refund_amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                            <th class="text-end text-nowrap"><?php echo $this->lang->line('net_amount') ?: 'Net Amount'; ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                            <th class="text-end text-nowrap noExport"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade sh-modal sh-modal-accent" id="collectionModal" tabindex="-1" aria-labelledby="collectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="collectionModalLabel"><?php echo $this->lang->line('collection_list'); ?></h5>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-primary btn-sm" id="btn_print_collection_modal">
                        <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="pup-scroll-area">
                <div class="modal-body"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function showdate(value) {
        if (value == 'period') {
            $('#fromdate').removeClass('d-none');
            $('#todate').removeClass('d-none');
        } else {
            $('#fromdate').addClass('d-none');
            $('#todate').addClass('d-none');
        }
    }
</script>

<script>
    var isPrinting = false;

    function updateKpiCards(data) {
        var currency = '<?php echo $currency_symbol; ?>';
        if (data) {
            if (data.total_paid_formatted !== undefined) {
                $('#kpi_total_paid').text(data.total_paid_formatted);
            } else if (data.total_paid !== undefined) {
                $('#kpi_total_paid').text(currency + ' ' + parseFloat(data.total_paid).toFixed(2));
            }
            if (data.total_refund_formatted !== undefined) {
                $('#kpi_total_refund').text(data.total_refund_formatted);
            } else if (data.total_refund !== undefined) {
                $('#kpi_total_refund').text(currency + ' ' + parseFloat(data.total_refund).toFixed(2));
            }
            if (data.net_amount_formatted !== undefined) {
                $('#kpi_net_amount').text(data.net_amount_formatted);
            } else if (data.net_amount !== undefined) {
                $('#kpi_net_amount').text(currency + ' ' + parseFloat(data.net_amount).toFixed(2));
            }
        }
    }

    function printDepartmentWiseTransactionReport() {
        if (isPrinting) {
            return false;
        }
        isPrinting = true;

        var formData = new FormData($('#form_dwtr')[0]);
        var $btn = $('#btn_print_dwtr, #dept_trans_table_wrapper .buttons-print, #dept_trans_table_wrapper .buttons-pdf, #dept_trans_table_wrapper .btn-print, #dept_trans_table_wrapper .btn-pdf, .allajaxlist_wrapper .btn-print, .allajaxlist_wrapper .btn-pdf');
        $btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url(); ?>admin/transaction/print_departmentwisetransaction_report',
            type: "POST",
            data: formData,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            success: function (res) {
                $btn.prop('disabled', false);
                setTimeout(function() {
                    isPrinting = false;
                }, 2000);
                if (res.status === 'success' && res.html) {
                    popup(res.html);
                } else {
                    errorMsg('No data available to print');
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                isPrinting = false;
                errorMsg('Something went wrong generating the report.');
            }
        });
    }

    var currentCollectionDate = '';
    var currentCollectionDept = '';

    function printCollectionList(date, dept) {
        if (isPrinting) {
            return false;
        }
        isPrinting = true;

        var $btn = $('#btn_print_collection_modal, #collectionModal .buttons-print, #collectionModal .buttons-pdf');
        $btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url(); ?>admin/transaction/print_collection_list',
            type: "POST",
            data: { 'date': date, 'department': dept },
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false);
                setTimeout(function() {
                    isPrinting = false;
                }, 2000);
                if (res.status === 'success' && res.html) {
                    popup(res.html);
                } else {
                    errorMsg('No data available to print');
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                isPrinting = false;
                errorMsg('Something went wrong generating the report.');
            }
        });
    }

    $(document).ready(function (e) {
        emptyDatatable('allajaxlist', 'data');

        $(document).off('xhr.dt', '.allajaxlist').on('xhr.dt', '.allajaxlist', function (e, settings, json, xhr) {
            if (json) {
                updateKpiCards(json);
            }
        });

        $(document).off('click', '#btn_print_dwtr').on('click', '#btn_print_dwtr', function(e) {
            e.preventDefault();
            printDepartmentWiseTransactionReport();
        });

        $(document).off('click', '#dept_trans_table_wrapper .buttons-print, #dept_trans_table_wrapper .buttons-pdf, #dept_trans_table_wrapper .btn-print, #dept_trans_table_wrapper .btn-pdf, .allajaxlist_wrapper .btn-print, .allajaxlist_wrapper .btn-pdf')
            .on('click', '#dept_trans_table_wrapper .buttons-print, #dept_trans_table_wrapper .buttons-pdf, #dept_trans_table_wrapper .btn-print, #dept_trans_table_wrapper .btn-pdf, .allajaxlist_wrapper .btn-print, .allajaxlist_wrapper .btn-pdf', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                printDepartmentWiseTransactionReport();
                return false;
            });

        $(document).off('click', '#btn_print_collection_modal').on('click', '#btn_print_collection_modal', function(e) {
            e.preventDefault();
            printCollectionList(currentCollectionDate, currentCollectionDept);
        });

        $(document).off('click', '#collectionModal .buttons-print, #collectionModal .buttons-pdf')
            .on('click', '#collectionModal .buttons-print, #collectionModal .buttons-pdf', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                printCollectionList(currentCollectionDate, currentCollectionDept);
                return false;
            });

        $(document).on('click', '.dept_collection', function (e) {
            var $btn = $(this);
            e.preventDefault();
            var dateText = $(this).closest('tr').find('td:first').text().trim();
            var deptName = $(this).data('department-name') || '';
            currentCollectionDate = $(this).data('date');
            currentCollectionDept = $(this).data('department');

            $.ajax({
                url: baseurl + 'admin/transaction/gettransactionbydate',
                type: "POST",
                data: { 'date': currentCollectionDate, 'department': currentCollectionDept },
                dataType: 'json',
                beforeSend: function () {
                    $btn.btnLoading();
                },
                success: function (data) {
                    $btn.btnReset();
                    $('#collectionModal .modal-body').html(data.page);
                    var modalTitle = dateText;
                    if (deptName) {
                        modalTitle += ' (' + deptName + ')';
                    }
                    $('#collectionModalLabel').text(modalTitle);
                    $('#collectionModal .example').DataTable({
                        dom: "Bfrtip",
                        buttons: [
                            { extend: 'copyHtml5', text: '<i class="fa fa-files-o"></i>', titleAttr: 'Copy', title: $('.download_label').html(), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i>', titleAttr: 'Excel', title: $('.download_label').html(), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                            { extend: 'csvHtml5', text: '<i class="fa fa-file-text-o"></i>', titleAttr: 'CSV', title: $('.download_label').html(), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i>',
                                titleAttr: 'PDF',
                                action: function (e, dt, node, config) {
                                    printCollectionList(currentCollectionDate, currentCollectionDept);
                                }
                            },
                            {
                                extend: 'print',
                                text: '<i class="fa fa-print"></i>',
                                titleAttr: 'Print',
                                action: function (e, dt, node, config) {
                                    printCollectionList(currentCollectionDate, currentCollectionDept);
                                }
                            }
                        ]
                    });
                    shModal('collectionModal').show();
                },
                error: function () {
                    alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    $btn.btnReset();
                },
                complete: function () {
                    $btn.btnReset();
                }
            });
        });
    });

    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('#form_dwtr').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('search', 'search_filter');
                $.ajax({
                    url: '<?php echo base_url(); ?>admin/transaction/checkvalidationdepartment',
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        if (data.status == "fail") {
                            $.each(data.error, function (key, value) {
                                $('#error_' + key).html(value);
                            });
                        } else {
                            $("#error_search_type").html('');
                            initDatatable('allajaxlist', 'admin/transaction/dtdepartmentwisetransactionreport/', data.param, [], 100, [
                                { "aTargets": [0, 1, 2], 'sClass': 'text-nowrap' },
                                { "aTargets": [3, 4, 5, 6], 'sClass': 'dt-body-right text-nowrap' }
                            ]);
                        }
                    }
                });
            });

            // Initial auto-search on load
            $('#form_dwtr').trigger('submit');
        });
    }(jQuery));
</script>
