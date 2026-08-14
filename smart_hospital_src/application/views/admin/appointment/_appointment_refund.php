<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
?>
<div class="modal fade" id="appointmentRefundModal" tabindex="-1" role="dialog" aria-labelledby="appointmentRefundModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="appointmentRefundModalLabel"><i class="fa fa-undo me-2"></i><?php echo $this->lang->line('refund'); ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="appointment_refund_form" accept-charset="utf-8" action="<?php echo base_url(); ?>admin/appointment/add_refund" method="post">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="ref_appointment_id" value="">
                    <input type="hidden" name="patient_id" id="ref_patient_id" value="">
                    <input type="hidden" id="ref_max_refundable" value="0">

                    <div class="alert alert-info py-2 px-3 mb-3 fs-13">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><strong><?php echo $this->lang->line('paid_amount'); ?>:</strong></span>
                            <span id="ref_paid_amount_text">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span><strong>Already Refunded:</strong></span>
                            <span id="ref_already_refunded_text">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Max Refundable:</strong></span>
                            <span id="ref_max_refundable_text" class="fw-bold text-success">0.00</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Refund Type</label><small class="req"> *</small>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="refund_type" id="ref_type_full" value="full" checked onclick="toggleRefundType('full')">
                                <label class="form-check-input-label" for="ref_type_full">Full Refund</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="refund_type" id="ref_type_partial" value="partial" onclick="toggleRefundType('partial')">
                                <label class="form-check-input-label" for="ref_type_partial">Partial (Custom Amount)</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('amount') . " (" . $currency_symbol . ")"; ?></label><small class="req"> *</small>
                                <input type="number" step="any" name="amount" id="ref_amount" class="form-control" placeholder="0.00" readonly required>
                                <span class="text-danger" id="ref_amount_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('date'); ?></label><small class="req"> *</small>
                                <input type="text" name="payment_date" id="ref_payment_date" value="<?php echo date($this->customlib->getHospitalDateFormat()); ?>" class="form-control date" autocomplete="off" required>
                                <span class="text-danger" id="ref_date_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('payment_mode'); ?></label><small class="req"> *</small>
                                <select class="form-select" name="payment_mode" id="ref_payment_mode">
                                    <?php if(isset($payment_mode)) { foreach ($payment_mode as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php if ($key == 'Cash') { echo "selected"; } ?>><?php echo $value; ?></option>
                                    <?php } } else { ?>
                                        <option value="Cash" selected>Cash</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Online">Online</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Card">Card</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label><?php echo $this->lang->line('note'); ?></label>
                                <textarea name="note" id="ref_note" class="form-control" rows="2" placeholder="Reason for refund..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" id="appointment_refund_btn" class="btn btn-info btn-sm"><i class="fa fa-check-circle me-1"></i><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function openAppointmentRefundModal(appointmentId, paidAmount, alreadyRefunded, maxRefundable, patientId) {
    $('#ref_appointment_id').val(appointmentId);
    $('#ref_patient_id').val(patientId || '');
    $('#ref_max_refundable').val(maxRefundable);
    
    $('#ref_paid_amount_text').text(parseFloat(paidAmount).toFixed(2));
    $('#ref_already_refunded_text').text(parseFloat(alreadyRefunded).toFixed(2));
    $('#ref_max_refundable_text').text(parseFloat(maxRefundable).toFixed(2));
    
    $('#ref_type_full').prop('checked', true);
    toggleRefundType('full');
    $('#ref_amount_error').text('');
    
    if (parseFloat(maxRefundable) <= 0) {
        $('#appointment_refund_btn').prop('disabled', true);
        $('#ref_amount_error').text('No refundable balance available for this appointment.');
    } else {
        $('#appointment_refund_btn').prop('disabled', false);
    }

    $('#appointmentRefundModal').modal('show');
}

function toggleRefundType(type) {
    var maxVal = parseFloat($('#ref_max_refundable').val()) || 0;
    if (type === 'full') {
        $('#ref_amount').val(maxVal.toFixed(2)).prop('readonly', true);
    } else {
        $('#ref_amount').val('').prop('readonly', false).focus();
    }
}

$(document).ready(function() {
    $('#appointment_refund_form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#appointment_refund_btn');
        var maxVal = parseFloat($('#ref_max_refundable').val()) || 0;
        var enteredVal = parseFloat($('#ref_amount').val()) || 0;

        if (enteredVal <= 0 || enteredVal > maxVal) {
            $('#ref_amount_error').text('Refund amount must be greater than 0 and cannot exceed ' + maxVal.toFixed(2));
            return false;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Processing...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(data) {
                btn.prop('disabled', false).html('<i class="fa fa-check-circle me-1"></i><?php echo $this->lang->line("save"); ?>');
                if (data.status === 'success') {
                    $('#appointmentRefundModal').modal('hide');
                    if (typeof successMsg === 'function') { successMsg(data.message); }
                    if (typeof initDatatable === 'function') { initDatatable(); }
                    else if (typeof table !== 'undefined' && table.ajax) { table.ajax.reload(); }
                    else { location.reload(); }
                } else {
                    if (typeof data.error === 'object') {
                        var errStr = '';
                        $.each(data.error, function(k, v) { if (v) errStr += v + ' '; });
                        $('#ref_amount_error').text(errStr);
                    } else {
                        $('#ref_amount_error').text(data.error || 'Refund failed');
                    }
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-check-circle me-1"></i><?php echo $this->lang->line("save"); ?>');
                $('#ref_amount_error').text('An error occurred. Please try again.');
            }
        });
    });
});

function cancelAppointment(id) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        $.ajax({
            url: '<?php echo base_url(); ?>admin/appointment/cancel_appointment',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    if (typeof successMsg === 'function') { successMsg(data.message); }
                    if (typeof initDatatable === 'function') { initDatatable(); }
                    else if (typeof table !== 'undefined' && table.ajax) { table.ajax.reload(); }
                    else { location.reload(); }
                } else {
                    if (typeof errorMsg === 'function') { errorMsg(data.error); }
                }
            }
        });
    }
}
</script>
