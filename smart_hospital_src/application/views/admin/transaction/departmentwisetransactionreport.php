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
                <form id="form_dwtr" action="" method="post">
                    <div class="row">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('search_type'); ?></label><small class="req"> *</small>
                                <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($searchlist as $key => $search) { ?>
                                        <option value="<?php echo $key ?>" <?php if ((isset($search_type)) && ($search_type == $key)) { echo "selected"; } ?>><?php echo $search ?></option>
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
                            <th class="text-nowrap"><?php echo $this->lang->line('transaction_id'); ?></th>
                            <th><?php echo $this->lang->line('department'); ?></th>
                            <th><?php echo $this->lang->line('patient_name'); ?></th>
                            <th class="text-nowrap"><?php echo $this->lang->line('reference_no'); ?></th>
                            <th class="text-nowrap"><?php echo $this->lang->line('payment_mode'); ?></th>
                            <th class="text-end text-nowrap"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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

    $(document).ready(function (e) {
        emptyDatatable('allajaxlist', 'data');

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
                                { "aTargets": [0, 1, 4, 5], 'sClass': 'text-nowrap' },
                                { "aTargets": [-1], 'sClass': 'dt-body-right text-nowrap' }
                            ]);
                        }
                    }
                });
            });
        });
    }(jQuery));
</script>
