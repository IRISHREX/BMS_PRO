<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <?php $this->load->view('admin/report/_finance'); ?>
            <div class="card-header ptbnull"></div>
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title"><?php echo $this->lang->line('daily_transaction_report'); ?></h3>
                <?php if (isset($result)) { ?>
                <button type="button" class="btn btn-primary btn-sm ms-auto" id="btn_print_dtr">
                    <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                </button>
                <?php } ?>
            </div>
            <div class="card-body pb-0">
                <form id="transaction_form" action="" method="post">
                    <div class="row">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_from'); ?></label><small class="req"> *</small>
                                <input id="date_from" name="date_from" type="text" class="form-control start_date" value="<?php echo set_value('date_from', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger" id="error_date_from"><?php echo form_error('date_from'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_to'); ?></label><small class="req"> *</small>
                                <input id="date_to" name="date_to" type="text" class="form-control end_date" value="<?php echo set_value('date_to', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger" id="error_date_to"><?php echo form_error('date_to'); ?></span>
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
            </div>
            <?php if (isset($result)) { ?>
            <div class="card-body table-responsive pt-0">
                <div class="download_label"><?php echo $this->lang->line('daily_transaction_report'); ?></div>
                <table class="table table-striped table-bordered table-hover example" id="daily_trans_table" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('date'); ?></th>
                            <th><?php echo $this->lang->line('total_transaction'); ?></th>
                            <th class="text-end"><?php echo $this->lang->line('online') . '(' . $currency_symbol . ')'; ?></th>
                            <th class="text-end"><?php echo $this->lang->line('offline') . '(' . $currency_symbol . ')'; ?></th>
                            <th class="text-end"><?php echo $this->lang->line('refund_amount') . '(' . $currency_symbol . ')'; ?></th>
                            <th class="text-end"><?php echo $this->lang->line('amount') . '(' . $currency_symbol . ')'; ?></th>
                            <th class="text-end noExport"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $dt_value) { ?>
                        <tr>
                            <td><?php echo $this->customlib->YYYYmmddTodateformat($dt_value['date']); ?></td>
                            <td><?php echo $dt_value['total_transaction']; ?></td>
                            <td class="text-end"><?php echo amountFormat($dt_value['online_transaction']); ?></td>
                            <td class="text-end"><?php echo amountFormat($dt_value['offline_transaction']); ?></td>
                            <td class="text-end"><?php echo ($dt_value['refund_amount'] > 0 ? '-' : '') . amountFormat($dt_value['refund_amount']); ?></td>
                            <td class="text-end"><?php echo amountFormat($dt_value['amount']); ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-secondary btn-sm daily_collection" data-bs-toggle="tooltip" data-date="<?php echo $dt_value['date']; ?>" title="<?php echo $this->lang->line('view_collection'); ?>" autocomplete="off"><i class="fa fa-list"></i></button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
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
var isPrinting = false;

function printDailyTransactionReport() {
    if (isPrinting) {
        return false;
    }
    isPrinting = true;

    var formData = new FormData($('#transaction_form')[0]);
    var $btn = $('#btn_print_dtr, #daily_trans_table_wrapper .buttons-print, #daily_trans_table_wrapper .buttons-pdf');
    $btn.prop('disabled', true);

    $.ajax({
        url: '<?php echo base_url(); ?>admin/transaction/print_dailytransaction_report',
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

    $(document).ready(function () {
        $(document).off('click', '#btn_print_dtr').on('click', '#btn_print_dtr', function(e) {
            e.preventDefault();
            printDailyTransactionReport();
        });

        setTimeout(function() {
            try {
                var dt = $('#daily_trans_table').DataTable();
                if (dt && dt.button) {
                    if (dt.button('.buttons-print').length) {
                        dt.button('.buttons-print').action(function (e, dtInstance, node, config) {
                            printDailyTransactionReport();
                        });
                    }
                    if (dt.button('.buttons-pdf').length) {
                        dt.button('.buttons-pdf').action(function (e, dtInstance, node, config) {
                            printDailyTransactionReport();
                        });
                    }
                    if (dt.button('.btn-print').length) {
                        dt.button('.btn-print').action(function (e, dtInstance, node, config) {
                            printDailyTransactionReport();
                        });
                    }
                    if (dt.button('.btn-pdf').length) {
                        dt.button('.btn-pdf').action(function (e, dtInstance, node, config) {
                            printDailyTransactionReport();
                        });
                    }
                }
            } catch(err) {}
        }, 200);

        $(document).off('click', '#daily_trans_table_wrapper .buttons-print, #daily_trans_table_wrapper .buttons-pdf, #daily_trans_table_wrapper .btn-print, #daily_trans_table_wrapper .btn-pdf')
            .on('click', '#daily_trans_table_wrapper .buttons-print, #daily_trans_table_wrapper .buttons-pdf, #daily_trans_table_wrapper .btn-print, #daily_trans_table_wrapper .btn-pdf', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                printDailyTransactionReport();
                return false;
            });

        document.querySelectorAll('.start_date').forEach(function (startEl) {
            if (!startEl.classList.contains('date')) startEl.classList.add('date');
            SHPicker.onChange(startEl, function (e) {
                if (!e.date) return;
                document.querySelectorAll('.end_date').forEach(function (endEl) {
                    SHPicker.setRestriction(endEl, { minDate: e.date });
                });
            });
        });
        document.querySelectorAll('.end_date').forEach(function (endEl) {
            if (!endEl.classList.contains('date')) endEl.classList.add('date');
            SHPicker.onChange(endEl, function (e) {
                if (!e.date) return;
                document.querySelectorAll('.start_date').forEach(function (startEl) {
                    SHPicker.setRestriction(startEl, { maxDate: e.date });
                });
            });
        });
    });

    var currentCollectionDate = '';

    function printCollectionList(date) {
        if (isPrinting) {
            return false;
        }
        isPrinting = true;

        var $btn = $('#btn_print_collection_modal, #collectionModal .buttons-print, #collectionModal .buttons-pdf');
        $btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url(); ?>admin/transaction/print_collection_list',
            type: "POST",
            data: { 'date': date },
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

    $(document).off('click', '#btn_print_collection_modal').on('click', '#btn_print_collection_modal', function(e) {
        e.preventDefault();
        printCollectionList(currentCollectionDate);
    });

    $(document).off('click', '#collectionModal .buttons-print, #collectionModal .buttons-pdf')
        .on('click', '#collectionModal .buttons-print, #collectionModal .buttons-pdf', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            printCollectionList(currentCollectionDate);
            return false;
        });

    $(document).on('click', '.daily_collection', function (e) {
        var $btn = $(this);
        e.preventDefault();
        var dateText = $(this).closest('tr').find('td:first').text().trim();
        currentCollectionDate = $(this).data('date');

        $.ajax({
            url: baseurl + 'admin/transaction/gettransactionbydate',
            type: "POST",
            data: { 'date': currentCollectionDate },
            dataType: 'json',
            beforeSend: function () {
                $btn.btnLoading();
            },
            success: function (data) {
                $btn.btnReset();
                $('#collectionModal .modal-body').html(data.page);
                $('#collectionModalLabel').text(dateText);
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
                                printCollectionList(currentCollectionDate);
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fa fa-print"></i>',
                            titleAttr: 'Print',
                            action: function (e, dt, node, config) {
                                printCollectionList(currentCollectionDate);
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
</script>
