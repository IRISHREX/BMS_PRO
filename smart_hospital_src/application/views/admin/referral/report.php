<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <?php $this->load->view('admin/report/_finance'); ?>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><?php echo $this->lang->line('referral_report'); ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm d-none" id="btn_print_statement">
                        <i class="fa fa-print me-1"></i> <?php echo $this->lang->line('print'); ?>
                    </button>
                </div>
            </div>
            <div class="card-body pb-0">
                <form id="form1" action="" method="post">
                    <div class="row">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('search_type'); ?></label><small class="req"> *</small>
                                <select class="form-control" name="search_type" id="search_type_select" onchange="showdate(this.value)">
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
                                <label><?php echo $this->lang->line('date_from'); ?></label><small class="req"> *</small>
                                <input id="date_from" name="date_from" type="text" class="form-control date" value="<?php echo set_value('date_from', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger"><?php echo form_error('date_from'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none" id="todate">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date_to'); ?></label><small class="req"> *</small>
                                <input id="date_to" name="date_to" type="text" class="form-control date" value="<?php echo set_value('date_to', date($this->customlib->getHospitalDateFormat())); ?>" />
                                <span class="text-danger"><?php echo form_error('date_to'); ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('payee'); ?></label>
                                <select class="form-control select2 w-100" name="payee" id="payee">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($person as $key => $value) { ?>
                                        <option value="<?php echo (int)$value->person_id ?>"><?php echo html_escape($value->name) ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="error_payee"></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line("patient_type"); ?></label>
                                <select class="form-control select2 w-100" name="patient_type" id="patient_type">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($type as $key => $value) { ?>
                                        <option value="<?php echo $value["id"] ?>"><?php echo $this->lang->line($value["name"]); ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="error_patient_type"></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line("patient"); ?></label>
                                <select class="form-control select2 w-100" name="patient" id="patient">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($patients as $key => $value) { ?>
                                        <option value="<?php echo (int)$value["id"] ?>"><?php echo html_escape($value['patient_name']) . " (" . (int)$value['id'] . ")"; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="error_patient"></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('status'); ?></label>
                                <select class="form-control select2 w-100" name="status" id="status">
                                    <option value=""><?php echo $this->lang->line('all') ?: 'All'; ?></option>
                                    <option value="paid"><?php echo $this->lang->line('paid') ?: 'Paid'; ?></option>
                                    <option value="unpaid"><?php echo $this->lang->line('unpaid') ?: 'Unpaid'; ?></option>
                                </select>
                                <span class="text-danger" id="error_status"></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-auto d-flex align-items-end ps-md-3 pe-md-3 ms-auto">
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
                <div class="download_label"><?php echo $this->lang->line('referral_report'); ?></div>
                <table class="table table-striped table-bordered table-hover allajaxlist" data-export-title="<?php echo $this->lang->line('referral_report'); ?>">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('payee'); ?></th>
                            <th><?php echo $this->lang->line('patient_name'); ?></th>
                            <th><?php echo $this->lang->line('date'); ?></th>
                            <th><?php echo $this->lang->line('bill_no'); ?></th>
                            <th><?php echo $this->lang->line('status'); ?></th>
                            <th class="text-end" width="15%"><?php echo $this->lang->line('commission_percentage'); ?> (%)</th>
                            <th class="text-end" width="15%"><?php echo $this->lang->line('bill_amount') . ' (' . $currency_symbol . ')'; ?></th>
                            <th class="text-end" width="15%"><?php echo $this->lang->line('commission_amount') . ' (' . $currency_symbol . ')'; ?></th>
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
(function ($) {
    'use strict';

    var isPrinting = false;

    function printReferralReport() {
        if (isPrinting) {
            return false;
        }
        isPrinting = true;

        var formData = new FormData($('#form1')[0]);
        var $btn = $('#btn_print_statement, .allajaxlist_wrapper .btn-print, .allajaxlist_wrapper .btn-pdf');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: '<?php echo base_url(); ?>admin/referral/print_report',
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
        $('.select2').select2();
        emptyDatatable('allajaxlist', 'data');

        $(document).off('click', '#btn_print_statement').on('click', '#btn_print_statement', function(e) {
            e.preventDefault();
            printReferralReport();
        });

        $('#form1').on('submit', (function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: '<?php echo base_url(); ?>admin/referral/checkvalidation',
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
                        $("#error_payee").html('');
                        $("#error_patient_type").html('');
                        $("#error_patient").html('');
                        $("#error_status").html('');
                        var table = initDatatable('allajaxlist', 'admin/referral/referral_report/', data.param, [], 100, [
                            { "sWidth": "15%", "aTargets": [-1, -2, -3], 'sClass': 'dt-body-right' }
                        ]);
                        $('#btn_print_statement').removeClass('d-none');

                        setTimeout(function() {
                            try {
                                var dt = $('.allajaxlist').DataTable();
                                if (dt && dt.button) {
                                    if (dt.button('.btn-print').length) {
                                        dt.button('.btn-print').action(function (e, dtInstance, node, config) {
                                            printReferralReport();
                                        });
                                    }
                                    if (dt.button('.btn-pdf').length) {
                                        dt.button('.btn-pdf').action(function (e, dtInstance, node, config) {
                                            printReferralReport();
                                        });
                                    }
                                }
                            } catch(err) {}
                        }, 200);
                    }
                }
            });
        }));
    });
}(jQuery));
</script>
