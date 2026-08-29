<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
include(APPPATH . 'views/admin/shared/_print_css.php');
?>

<style>
@page {
    size: A5 portrait;
    margin: 8mm 10mm;
}
@media print {
    html, body {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Inter', Arial, sans-serif;
        font-size: 10.5px !important;
        color: #111 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .table-print-full {
        width: 100% !important;
        page-break-inside: avoid;
    }
    .content-body {
        padding: 0 2mm !important;
    }
    .fixed-print-header {
        max-height: 80px !important;
    }
    .fixed-print-header img {
        max-height: 80px !important;
        object-fit: contain !important;
    }
    .header-space {
        height: 80px !important;
    }
}

/* Appointment Receipt Specific Styles */
.sh-apt-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    margin-bottom: 8px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
}
.sh-apt-serial {
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.sh-apt-serial-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #475569;
    letter-spacing: 0.5px;
}
.sh-apt-serial-val {
    font-size: 20px;
    font-weight: 800;
    color: #0284c7;
    line-height: 1;
}
.sh-apt-status {
    display: flex;
    align-items: center;
    gap: 6px;
}
.sh-apt-status-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #475569;
    letter-spacing: 0.5px;
}
.sh-apt-status-badge {
    font-size: 13.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 12px;
    border-radius: 4px;
    display: inline-block;
    line-height: 1.2;
}
.sh-status-approved {
    background: #dcfce7 !important;
    color: #15803d !important;
    border: 1.5px solid #22c55e !important;
}
.sh-status-pending {
    background: #fef9c3 !important;
    color: #a16207 !important;
    border: 1.5px solid #eab308 !important;
}
.sh-status-cancel {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1.5px solid #ef4444 !important;
}
.sh-status-default {
    background: #f1f5f9 !important;
    color: #334155 !important;
    border: 1.5px solid #94a3b8 !important;
}

/* Patient Details Section - Left-aligned, perfectly positioned colons and non-breaking */
.sh-apt-info-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.sh-apt-info-table tr {
    vertical-align: top;
}
.sh-apt-info-table th {
    font-size: 9.5px;
    font-weight: 600;
    color: #334155;
    text-align: left !important;
    white-space: nowrap !important;
    padding: 3px 0 !important;
}
.sh-apt-info-table th::after {
    content: '' !important;
}
.sh-apt-info-table td.sh-colon {
    font-size: 9.5px;
    font-weight: 600;
    color: #475569;
    text-align: center !important;
    padding: 3px 0 !important;
    white-space: nowrap !important;
    width: 8px !important;
}
.sh-apt-info-table td.sh-val {
    font-size: 10.5px;
    font-weight: 700;
    color: #0f172a;
    text-align: left !important;
    padding: 3px 0 3px 6px !important;
    word-break: normal !important;
}
</style>

<div class="fixed-print-header">
    <?php if (!empty($print_details[0]['print_header'])) { ?>
        <img src="<?php echo $this->media_storage->getImageURL($print_details[0]['print_header']); ?>"
             class="img-fluid sh-avatar-cover" >
    <?php } ?>
</div>

<table class="table-print-full" width="100%">
    <thead>
        <tr>
            <td><div class="header-space">&nbsp;</div></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="content-body sh-px-12">
                <div class="print-area">

                    <div class="sh-print-title"><?php echo $this->lang->line('appointment_details'); ?></div>

                    <!-- Requirements 1 & 2: Serial Number at Top-Left, Status at Top-Right -->
                    <div class="sh-apt-topbar">
                        <div class="sh-apt-serial">
                            <span class="sh-apt-serial-label"><?php echo $this->lang->line('appointment_s_no'); ?>:</span>
                            <span class="sh-apt-serial-val">#<?php echo html_escape($result['appointment_serial_no'] ?: '-'); ?></span>
                        </div>
                        <div class="sh-apt-status">
                            <span class="sh-apt-status-label"><?php echo $this->lang->line('status'); ?>:</span>
                            <?php
                                $status_val = strtolower($result['appointment_status'] ?? '');
                                $status_cls = 'sh-status-default';
                                if ($status_val === 'approved') {
                                    $status_cls = 'sh-status-approved';
                                } elseif ($status_val === 'pending') {
                                    $status_cls = 'sh-status-pending';
                                } elseif ($status_val === 'cancel') {
                                    $status_cls = 'sh-status-cancel';
                                }
                            ?>
                            <span class="sh-apt-status-badge <?php echo $status_cls; ?>">
                                <?php echo html_escape($result['appointment_status'] ? $this->lang->line($result['appointment_status']) : '-'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="sh-print-info-block">
                        <div class="sh-flex-gap18">
                            <table class="sh-apt-info-table w-50">
                                <colgroup>
                                    <col style="width: 82px;">
                                    <col style="width: 8px;">
                                    <col>
                                </colgroup>
                                <tr>
                                    <th><?php echo $this->lang->line('patient_name'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result['patients_name'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('age') . '/' . $this->lang->line('gender'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result['age_gender'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('phone'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result['patient_mobileno'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('address'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result['patient_address'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('doctor'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape(composeStaffNameByString($result['name'], $result['surname'], $result['employee_id']) ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('collected_by'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php
                                        if (!empty($result['received_by']) && (int)$result['received_by'] > 0) {
                                            $staff_data = $this->staff_model->getstaff($result['received_by']);
                                            echo html_escape(($staff_data["name"] ?? '') . " " . ($staff_data["surname"] ?? '') . " (" . ($staff_data["employee_id"] ?? '') . ")");
                                        } else {
                                            echo '-';
                                        }
                                    ?></td>
                                </tr>
                            </table>

                            <table class="sh-apt-info-table w-50">
                                <colgroup>
                                    <col style="width: 105px;">
                                    <col style="width: 8px;">
                                    <col>
                                </colgroup>
                                <tr>
                                    <th><?php echo $this->lang->line('appointment_no'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result['appointment_no'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('appointment_date'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result["date"] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('shift') . '/' . $this->lang->line('slot'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val"><?php echo html_escape($result['shift_slot'] ?: '-') ?></td>
                                </tr>
                                <?php if (($result['payment_mode'] ?? '') == 'Cheque') { ?>
                                    <tr>
                                        <th><?php echo $this->lang->line('cheque_no'); ?></th>
                                        <td class="sh-colon">:</td>
                                        <td class="sh-val"><?php echo html_escape($result['cheque_no'] ?: '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo $this->lang->line('cheque_date'); ?></th>
                                        <td class="sh-colon">:</td>
                                        <td class="sh-val"><?php echo (!empty($result['cheque_date']) ? $this->customlib->YYYYMMDDTodateFormat($result['cheque_date'], $this->customlib->getHospitalTimeFormat()) : '-'); ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>

                        <?php if (!empty($result['message']) || !empty($fields)) { ?>
                            <div style="margin-top:8px;"></div>
                            <table class="sh-apt-info-table" style="width: 100%;">
                                <colgroup><col style="width: 82px;"><col style="width: 8px;"><col></colgroup>
                                <?php if (!empty($result['message'])) { ?>
                                <tr>
                                    <th><?php echo $this->lang->line('message'); ?></th>
                                    <td class="sh-colon">:</td>
                                    <td class="sh-val lh-normal"><?php echo html_escape($result['message']) ?></td>
                                </tr>
                                <?php } ?>
                                <?php if (!empty($fields)) { foreach ($fields as $fields_key => $fields_value) {
                                    $display_field = $result["$fields_value->name"] ?? '';
                                    if ($fields_value->type == "link") {
                                        $display_field = ($display_field !== '')
                                            ? "<a href=\"" . html_escape($display_field) . "\" target=\"_blank\">" . html_escape($display_field) . "</a>"
                                            : '-';
                                    } else {
                                        $display_field = html_escape($display_field ?: '-');
                                    }
                                ?>
                                    <tr>
                                        <th><?php echo html_escape($fields_value->name); ?></th>
                                        <td class="sh-colon">:</td>
                                        <td class="sh-val lh-normal"><?php echo $display_field; ?></td>
                                    </tr>
                                <?php } } ?>
                            </table>
                        <?php } ?>
                    </div>

                    <div class="sh-section-divider"></div>

                    <?php
                        $standard_amount    = isset($result['standard_amount']) && $result['standard_amount'] !== "" ? (float)$result['standard_amount'] : 0.0;
                        $discount_percentage = isset($result['discount_percentage']) && $result['discount_percentage'] !== "" ? (float)$result['discount_percentage'] : 0.0;
                        $discount_amt       = ($standard_amount * $discount_percentage) / 100;
                        $net_amount         = $standard_amount - $discount_amt;
                        $paid_amount        = isset($result['paid_amount']) && $result['paid_amount'] !== "" ? (float)$result['paid_amount'] : 0.0;
                        $refund_amount      = isset($result['refund_amount']) && $result['refund_amount'] !== "" ? (float)$result['refund_amount'] : 0.0;
                        $tax_display        = isset($result['tax']) && $result['tax'] !== "" ? $result['tax'] : 0;
                    ?>

                    <div class="sh-print-section-title"><?php echo $this->lang->line("payment_details"); ?></div>
                    <table class="sh-print-table">
                        <thead>
                            <tr>
                                <th style="width:38%"><?php echo $this->lang->line('transaction_id'); ?></th>
                                <th style="width:34%"><?php echo $this->lang->line('payment_mode'); ?></th>
                                <th class="sh-col-22 sh-text-right"><?php echo $this->lang->line('amount') . ' (' . $currency_symbol . ')'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo html_escape($result['transaction_id'] ?: '-') ?></td>
                                <td><?php echo (!empty($result['payment_mode']) ? $this->lang->line(strtolower($result['payment_mode'])) : ($result['payment_mode'] ?: '-')) ?></td>
                                <td class="sh-text-right"><?php echo $currency_symbol . amountFormat($standard_amount); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="p-0">
                                    <div style="border-bottom: 1px solid #cbd5e1;"></div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="sh-row-first">
                                <td colspan="2"><?php echo $this->lang->line('amount'); ?></td>
                                <td><?php echo $currency_symbol . amountFormat($standard_amount); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?php echo $this->lang->line('discount'); ?></td>
                                <td><?php echo $currency_symbol . amountFormat($discount_amt) . ' (' . amountFormat($discount_percentage) . ' %)'; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?php echo $this->lang->line('tax'); ?></td>
                                <td><?php echo $currency_symbol . amountFormat((float)$tax_display); ?></td>
                            </tr>
                            <tr class="sh-row-total">
                                <td colspan="2"><?php echo $this->lang->line('net_amount'); ?></td>
                                <td><?php echo $currency_symbol . amountFormat($net_amount); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?php echo $this->lang->line('paid_amount'); ?></td>
                                <td style="font-weight: 700;"><?php echo $currency_symbol . amountFormat($paid_amount); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?php echo $this->lang->line('refund_amount') ?: 'Refunded Amount'; ?></td>
                                <td style="font-weight: 700; color: <?php echo ($refund_amount > 0) ? '#dc2626' : '#111'; ?>;"><?php echo $currency_symbol . amountFormat($refund_amount); ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if (!empty($result['payment_note'])) { ?>
                    <div class="sh-note-box">
                        <span class="fw-semibold"><?php echo $this->lang->line('payment_note'); ?>: </span><?php echo html_escape($result['payment_note']); ?>
                    </div>
                    <?php } ?>

                </div>
                </div>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>
                <?php if (!empty($print_details[0]['print_footer'])) { ?>
                    <div class="footer-space">&nbsp;</div>
                <?php } ?>
            </td>
        </tr>
    </tfoot>
</table>

<?php if (!empty($print_details[0]['print_footer'])) { ?>
    <div class="footer-fixed">
        <?php echo $print_details[0]['print_footer']; ?>
    </div>
<?php } ?>