<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <?php $this->load->view('admin/report/_finance'); ?>
            <div class="card-header ptbnull"></div>
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title"><?php echo $this->lang->line('income_report'); ?></h3>
                <button type="button" class="btn btn-primary btn-sm ms-auto" id="btn_print_income_report">
                    <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                </button>
            </div>
            <div class="card-body pb-0">
                <form id="form1" action="" method="post">
                    <div class="row">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="col-sm-6 col-md-4">
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
                        <div class="col-sm-6 col-md-4 d-none" id="fromdate">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_from'); ?></label>
                                <input id="date_from" name="date_from" type="text" class="form-control date" value="<?php echo set_value('date_from', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger"><?php echo form_error('date_from'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 d-none" id="todate">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_to'); ?></label>
                                <input id="date_to" name="date_to" type="text" class="form-control date" value="<?php echo set_value('date_to', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger"><?php echo form_error('date_to'); ?></span>
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
            <div class="card-body table-responsive pt-0">
                <div class="download_label"><?php echo $this->lang->line('income_report'); ?></div>
                <table class="table table-striped table-bordered table-hover allajaxlist" id="income_table" data-export-title="<?php echo $this->lang->line('income_report'); ?>">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th><?php echo $this->lang->line('invoice_number'); ?></th>
                            <th><?php echo $this->lang->line('income_head'); ?></th>
                            <th><?php echo $this->lang->line('date'); ?></th>
                            <?php if (!empty($fields)) {
                                foreach ($fields as $fields_key => $fields_value) { ?>
                                    <th><?php echo $fields_value->name; ?></th>
                                <?php }
                            } ?>
                            <th class="text-end"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
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
    var base_url = '<?php echo base_url() ?>';

    function printDiv(elem) {
        popup(jQuery(elem).html());
    }

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

    function printIncomeReport() {
        if (isPrinting) {
            return false;
        }
        isPrinting = true;

        var formData = new FormData($('#form1')[0]);
        var $btn = $('#btn_print_income_report, #income_table_wrapper .buttons-print, #income_table_wrapper .buttons-pdf, #income_table_wrapper .btn-print, #income_table_wrapper .btn-pdf');
        $btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url(); ?>admin/income/print_income_report',
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

        $(document).off('click', '#btn_print_income_report').on('click', '#btn_print_income_report', function(e) {
            e.preventDefault();
            printIncomeReport();
        });

        $(document).off('click', '#income_table_wrapper .buttons-print, #income_table_wrapper .buttons-pdf, #income_table_wrapper .btn-print, #income_table_wrapper .btn-pdf, .allajaxlist_wrapper .btn-print, .allajaxlist_wrapper .btn-pdf')
            .on('click', '#income_table_wrapper .buttons-print, #income_table_wrapper .buttons-pdf, #income_table_wrapper .btn-print, #income_table_wrapper .btn-pdf, .allajaxlist_wrapper .btn-print, .allajaxlist_wrapper .btn-pdf', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                printIncomeReport();
                return false;
            });
    });

    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('#form1').on('submit', (function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('search', 'search_filter');
                $.ajax({
                    url: '<?php echo base_url(); ?>admin/income/checkvalidationincome',
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
                            initDatatable('allajaxlist', 'admin/income/incomereports/', data.param, [], 100, [
                                { "aTargets": [-1], 'sClass': 'dt-body-right' }
                            ]);
                        }
                    }
                });
            }));
        });
    }(jQuery));
</script>
