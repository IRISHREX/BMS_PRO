<?php
$currency_symbol        = $this->customlib->getHospitalCurrencyFormat();
$total_paid             = isset($radiology_total_payment) ? (float)$radiology_total_payment : (isset($radio_billing->total_deposit) ? (float)$radio_billing->total_deposit : 0);
$total_refund           = isset($radiology_total_refund) ? (float)$radiology_total_refund : (float)$this->transaction_model->getTotalRefundAmountByRadiologyBillId($radio_billing->id);
$net_paid               = max(0, round($total_paid - $total_refund, 2));
$max_refundable         = isset($max_refundable) ? (float)$max_refundable : $net_paid;
$action_type            = isset($action_type) && in_array($action_type, ['payment', 'refund']) ? $action_type : 'payment';

$balance_amount         = max(0, round($radio_billing->net_amount - $net_paid, 2));
$due_class              = ($balance_amount <= 0) ? 'sh-status-paid' : 'sh-status-due';
$denominator            = $radio_billing->total - $radio_billing->discount;
$tax_percentage         = ($denominator != 0) ? amountFormat(($radio_billing->tax * 100) / $denominator) : 0;
$has_payment_perm       = $this->rbac->hasPrivilege('radiology_billing_payment', 'can_add') || $this->rbac->hasPrivilege('radiology_partial_payment', 'can_add');
$is_refund_mode         = ($action_type === 'refund');
?>

<?php ob_start(); ?>
<div class="sh-form-card">
    <div class="sh-card-header"><span class="sh-card-header-title"><?php echo $this->lang->line('transaction_history'); ?></span></div>
    <?php if (!empty($radiology_transaction)) { ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover sh-tests-table mb-0">
            <thead>
                <tr>
                    <th><?php echo $this->lang->line('transaction_id'); ?></th>
                    <th><?php echo $this->lang->line('date'); ?></th>
                    <th><?php echo $this->lang->line('mode'); ?></th>
                    <th><?php echo $this->lang->line('note'); ?></th>
                    <th class="text-center"><?php echo $this->lang->line('amount'); ?></th>
                    <th class="text-end pe-3"><?php echo $this->lang->line('action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($radiology_transaction as $transaction_key => $transaction) { ?>
                <tr>
                    <td><?php echo $this->customlib->getSessionPrefixByType('transaction_id') . $transaction->id; ?></td>
                    <td class="text-nowrap"><?php echo $this->customlib->YYYYMMDDHisTodateFormat($transaction->payment_date, $this->customlib->getHospitalTimeFormat()); ?></td>
                    <td>
                        <?php echo $this->lang->line(strtolower($transaction->payment_mode));
                        if ($transaction->payment_mode == "Cheque") {
                            if ($transaction->cheque_no != '') { echo '<br><small class="text-secondary">' . $this->lang->line('cheque_no') . ': ' . $transaction->cheque_no . '</small>'; }
                            if ($transaction->cheque_date != '' && $transaction->cheque_date != '0000-00-00') { echo '<br><small class="text-secondary">' . $this->lang->line('cheque_date') . ': ' . $this->customlib->YYYYMMDDTodateFormat($transaction->cheque_date) . '</small>'; }
                        } ?>
                    </td>
                    <td><?php echo html_escape($transaction->note); ?></td>
                    <td class="text-center fw-semibold">
                        <?php if (isset($transaction->type) && $transaction->type === 'refund') { ?>
                            <span class="text-danger">- <?php echo $currency_symbol . amountFormat($transaction->amount); ?></span> <span class="badge bg-danger ms-1" style="font-size:10px;"><?php echo $this->lang->line('refund'); ?></span>
                        <?php } else { ?>
                            <span class="text-success"><?php echo $currency_symbol . amountFormat($transaction->amount); ?></span>
                        <?php } ?>
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-inline-flex gap-1">
                            <?php if ($transaction->payment_mode == "Cheque" && $transaction->attachment != "") { ?>
                            <a href='<?php echo site_url('admin/transaction/download/' . $transaction->id); ?>' class='btn btn-sm btn-light' data-bs-toggle='tooltip' title='<?php echo $this->lang->line('download'); ?>'><i class='fa fa-download'></i></a>
                            <?php } ?>
                            <?php if ($is_bill) { ?>
                            <a href='javascript:void(0)' data-loading-text='<i class="fa fa-circle-o-notch fa-spin"></i>' data-record-id="<?php echo $transaction->id; ?>" class='btn btn-sm btn-light print_radio_receipt' data-bs-toggle='tooltip' title='<?php echo $this->lang->line('print'); ?>'><i class='fa fa-print'></i></a>
                            <?php } else { ?>
                            <a href='javascript:void(0)' data-loading-text='<i class="fa fa-circle-o-notch fa-spin"></i>' data-record-id="<?php echo $transaction->id; ?>" class='btn btn-sm btn-light print_receipt' data-bs-toggle='tooltip' title='<?php echo $this->lang->line('print'); ?>'><i class='fa fa-print'></i></a>
                            <?php if (($this->rbac->hasPrivilege('radiology_billing_payment', 'can_delete')) || ($this->rbac->hasPrivilege('radiology_partial_payment', 'can_delete'))) { ?>
                            <a href='javascript:void(0)' data-loading-text='<i class="fa fa-circle-o-notch fa-spin"></i>' data-record-id="<?php echo $transaction->id; ?>" class='btn btn-sm btn-light delete_trans' data-bs-toggle='tooltip' title='<?php echo $this->lang->line('delete'); ?>'><i class='fa fa-trash text-danger'></i></a>
                            <?php } ?>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } else { ?>
    <div class="px-3 py-3">
        <div class="alert alert-info mb-0"><?php echo $this->lang->line('no_record_found'); ?></div>
    </div>
    <?php } ?>
</div>
<?php $tx_html = ob_get_clean(); ?>

<?php ob_start(); ?>
        <div class="sh-card-header">
            <span class="sh-card-header-title"><?php echo $this->lang->line('patient'); ?> &amp; <?php echo $this->lang->line('bill_details'); ?></span>
        </div>
        <div class="sh-info-grid">
            <div class="row g-0">
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('bill_no'); ?></div>
                    <div class="sh-info-value highlight"><?php echo $this->customlib->getSessionPrefixByType('radiology_billing') . $radio_billing->id; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('patient'); ?></div>
                    <div class="sh-info-value highlight"><?php echo html_escape($radio_billing->patient_name . ' (' . $radio_billing->patient_id . ')'); ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('case_id'); ?></div>
                    <div class="sh-info-value"><?php echo $radio_billing->case_reference_id ?: '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('prescription_no'); ?></div>
                    <div class="sh-info-value">—</div>
                </div>
            </div>
            <div class="row g-0 sh-row-divider">
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('age'); ?></div>
                    <div class="sh-info-value"><?php echo $this->customlib->getPatientAge($radio_billing->age, $radio_billing->month, $radio_billing->day) ?: '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('gender'); ?></div>
                    <div class="sh-info-value"><?php echo $radio_billing->gender ? $this->lang->line(strtolower($radio_billing->gender)) : '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('mobile_no'); ?></div>
                    <div class="sh-info-value"><?php echo $radio_billing->mobileno ?: '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('email'); ?></div>
                    <div class="sh-info-value"><?php echo $radio_billing->email ?: '—'; ?></div>
                </div>
            </div>
            <div class="row g-0 sh-row-divider">
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('doctor_name'); ?></div>
                    <div class="sh-info-value"><?php echo html_escape($radio_billing->doctor_name) ?: '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('generated_by'); ?></div>
                    <div class="sh-info-value"><?php echo composeStaffNameByString($radio_billing->name, $radio_billing->surname, $radio_billing->employee_id) ?: '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('blood_group'); ?></div>
                    <div class="sh-info-value"><?php echo html_escape($radio_billing->blood_group_name) ?: '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('note'); ?></div>
                    <div class="sh-info-value"><?php echo !empty($radio_billing->note) ? html_escape($radio_billing->note) : '—'; ?></div>
                </div>
            </div>
            <div class="row g-0 sh-row-divider">
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('tpa'); ?></div>
                    <div class="sh-info-value"><?php echo !empty($radio_billing->organisation_name) ? html_escape($radio_billing->organisation_name) : '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('tpa_id'); ?></div>
                    <div class="sh-info-value"><?php echo !empty($radio_billing->insurance_id) ? html_escape($radio_billing->insurance_id) : '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('tpa_validity'); ?></div>
                    <div class="sh-info-value"><?php echo !empty($radio_billing->insurance_validity) ? $this->customlib->YYYYMMDDTodateFormat($radio_billing->insurance_validity) : '—'; ?></div>
                </div>
                <div class="col-6 col-md-3 sh-info-item">
                    <div class="sh-info-label"><?php echo $this->lang->line('address'); ?></div>
                    <div class="sh-info-value"><?php echo html_escape($radio_billing->address) ?: '—'; ?></div>
                </div>
            </div>
        </div>
<?php $patient_info_html = ob_get_clean(); ?>

<?php ob_start(); ?>
        <div class="sh-card-header">
            <span class="sh-card-header-title"><?php echo $this->lang->line('bill_summary'); ?></span>
        </div>
        <div class="sh-summary-row">
            <span class="text-secondary"><?php echo $this->lang->line('total'); ?></span>
            <span><?php echo $currency_symbol . amountFormat($radio_billing->total); ?></span>
        </div>
        <div class="sh-summary-row">
            <span class="text-secondary"><?php echo $this->lang->line('total_discount'); ?></span>
            <span class="text-danger">
                <?php if ($radio_billing->discount > 0) {
                    echo '- ' . $currency_symbol . amountFormat($radio_billing->discount);
                    if ($radio_billing->discount_percentage > 0) {
                        echo ' <small class="text-secondary">(' . $radio_billing->discount_percentage . '%)</small>';
                    }
                } else { echo '—'; } ?>
            </span>
        </div>
        <div class="sh-summary-row">
            <span class="text-secondary"><?php echo $this->lang->line('total_tax'); ?></span>
            <span>
                <?php echo ($radio_billing->tax > 0)
                    ? $currency_symbol . amountFormat($radio_billing->tax) . ' <small class="text-secondary">(' . $tax_percentage . '%)</small>'
                    : '—'; ?>
            </span>
        </div>
        <div class="sh-summary-netamt">
            <span class="fw-bold"><?php echo $this->lang->line('net_amount'); ?></span>
            <span class="fw-bold fs-6"><?php echo $currency_symbol . amountFormat($radio_billing->net_amount); ?></span>
        </div>
        <div class="sh-summary-row">
            <span class="text-secondary"><i class="fa fa-check-circle text-success me-1"></i><?php echo $this->lang->line('paid_amount'); ?></span>
            <span class="text-success fw-semibold"><?php echo $currency_symbol . amountFormat($net_paid); ?></span>
        </div>
        <?php if ($total_refund > 0) { ?>
        <div class="sh-summary-row">
            <span class="text-secondary"><i class="fa fa-reply text-danger me-1"></i><?php echo $this->lang->line('refund'); ?></span>
            <span class="text-danger fw-semibold"><?php echo $currency_symbol . amountFormat($total_refund); ?></span>
        </div>
        <?php } ?>
        <div class="sh-due-row <?php echo $due_class; ?>">
            <span><?php echo $this->lang->line('due_amount'); ?></span>
            <span><?php echo $currency_symbol . amountFormat($balance_amount); ?></span>
        </div>
<?php $net_amount_html = ob_get_clean(); ?>

<?php if ($has_payment_perm) { ?>

<!-- RBAC TRUE: Patient Info (full width) → [Net Amount | Payment/Refund Form] -->
<div class="sh-form-card mb-2"><?php echo $patient_info_html; ?></div>

<div class="d-flex gap-2 mb-2 flex-wrap">
    <div class="sh-vd-sum-wrap">
        <div class="sh-form-card h-100 overflow-hidden"><?php echo $net_amount_html; ?></div>
    </div>
    <div class="sh-flex-col">
        <div class="sh-form-card h-100">
            <div class="sh-card-header">
                <span class="sh-card-header-title">
                    <?php echo $is_refund_mode ? $this->lang->line('refund') : $this->lang->line('add_payment'); ?>
                </span>
            </div>
            <div class="px-3 py-3">
                <form id="<?php echo $form_id; ?>" action="<?php echo site_url($is_refund_mode ? 'admin/radio/partial_refund' : 'admin/radio/partialbill'); ?>" accept-charset="utf-8" method="post">
                    <input type="hidden" name="radiology_billing_id" value="<?php echo $radio_billing->id; ?>">
                    <input type="hidden" name="case_reference_id" value="<?php echo $radio_billing->case_reference_id; ?>">
                    <input type="hidden" name="patient_id" value="<?php echo $radio_billing->patient_id; ?>">
                    
                    <?php if ($is_refund_mode) { ?>
                        <input type="hidden" name="action_type" value="refund">
                        <div class="alert alert-info py-1 px-2 mb-2 small d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-info-circle me-1"></i>Max Refundable:</span>
                            <strong id="radio_max_refund_badge"><?php echo $currency_symbol . amountFormat($max_refundable); ?></strong>
                        </div>
                    <?php } ?>

                    <div class="row g-2">
                        <?php if (!$is_refund_mode) { ?>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('type'); ?><small class="req"> *</small></label>
                            <select class="form-control form-select-sm" id="radio_action_type" name="action_type" onchange="radioToggleRefundStatus(this, '<?php echo $form_id; ?>', <?php echo $max_refundable; ?>, <?php echo $balance_amount; ?>)">
                                <option value="payment" selected><?php echo $this->lang->line('payment'); ?></option>
                                <?php if ($max_refundable > 0) { ?>
                                <option value="refund"><?php echo $this->lang->line('refund'); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php } ?>

                        <div class="col-sm-6 radio_appt_status_wrap" style="<?php echo $is_refund_mode ? '' : 'display:none;'; ?>">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('status'); ?><small class="req"> *</small></label>
                            <select class="form-control form-select-sm" name="appointment_status" id="radio_appointment_status">
                                <option value="approved">Refunded &amp; Active</option>
                                <option value="cancelled">Refunded &amp; Cancelled</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('date'); ?><small class="req"> *</small></label>
                            <input type="text" name="payment_date" id="date" class="form-control form-control-sm datetime no-past-date" data-min-date="today">
                            <span class="text-danger"><?php echo form_error('payment_date'); ?></span>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('amount') . ' (' . $currency_symbol . ')'; ?><small class="req"> *</small></label>
                            <input type="text" name="amount" id="amount" class="form-control form-control-sm" 
                                value="<?php echo amountFormat($is_refund_mode ? $max_refundable : ($balance_amount > 0 ? $balance_amount : 0)); ?>" 
                                data-max-refundable="<?php echo $max_refundable; ?>"
                                data-balance-amount="<?php echo $balance_amount; ?>"
                                onkeyup="radioCheckRefundAmount(this, '<?php echo $form_id; ?>')">
                            <span class="text-danger"><?php echo form_error('amount'); ?></span>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('payment_mode'); ?></label>
                            <select class="form-control form-select-sm payment_mode" name="payment_mode">
                                <?php foreach ($payment_mode as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo ($key == 'cash') ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('note'); ?></label>
                            <textarea name="note" id="note" class="form-control form-control-sm" rows="1"><?php echo $is_refund_mode ? 'Refund processed' : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="row g-2 cheque_div mt-1 d-none">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('cheque_no'); ?><small class="req"> *</small></label>
                            <input type="text" name="cheque_no" id="cheque_no" class="form-control form-control-sm">
                            <span class="text-danger"><?php echo form_error('cheque_no'); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('cheque_date'); ?><small class="req"> *</small></label>
                            <input type="text" name="cheque_date" id="cheque_date" class="form-control form-control-sm date">
                            <span class="text-danger"><?php echo form_error('cheque_date'); ?></span>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1"><?php echo $this->lang->line('attach_document'); ?></label>
                            <input type="file" class="filestyle form-control form-control-sm" name="document">
                            <span class="text-danger"><?php echo form_error('document'); ?></span>
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" data-loading-text="<?php echo $this->lang->line('processing'); ?>" class="btn btn-sm btn-info">
                            <i class="fa fa-check-circle me-1"></i><?php echo $this->lang->line('save'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function radioToggleRefundStatus(sel, formId, maxRefund, dueAmount) {
    var isRefund = sel.value === 'refund';
    var form = $('#' + formId);
    form.attr('action', isRefund
        ? '<?php echo site_url('admin/radio/partial_refund'); ?>'
        : '<?php echo site_url('admin/radio/partialbill'); ?>'
    );
    form.find('.radio_appt_status_wrap').toggle(isRefund);
    form.find('[name="appointment_status"]').prop('disabled', !isRefund);
    if (isRefund) {
        form.find('#amount').val(parseFloat(maxRefund).toFixed(2));
        form.closest('.sh-flex-col').find('.sh-card-header-title').text('<?php echo $this->lang->line('refund'); ?>');
    } else {
        form.find('#amount').val(parseFloat(dueAmount).toFixed(2));
        form.closest('.sh-flex-col').find('.sh-card-header-title').text('<?php echo $this->lang->line('add_payment'); ?>');
    }
}

function radioCheckRefundAmount(input, formId) {
    var form = $('#' + formId);
    var actionType = form.find('[name="action_type"]').val();
    if (actionType === 'refund') {
        var maxRefund = parseFloat($(input).data('maxRefundable') || 0);
        var entered = parseFloat($(input).val() || 0);
        if (entered >= maxRefund && maxRefund > 0) {
            form.find('#radio_appointment_status').val('cancelled');
        } else {
            form.find('#radio_appointment_status').val('approved');
        }
    }
}
</script>

<?php } else { ?>

<!-- RBAC FALSE: [Patient Info (flex-1)] | [Net Amount (234px)] side by side -->
<div class="d-flex gap-2 mb-2 flex-wrap">
    <div class="sh-flex-col">
        <div class="sh-form-card h-100"><?php echo $patient_info_html; ?></div>
    </div>
    <div class="sh-vd-sum-wrap">
        <div class="sh-form-card h-100 overflow-hidden"><?php echo $net_amount_html; ?></div>
    </div>
</div>

<?php } ?>

<?php echo $tx_html; ?>
