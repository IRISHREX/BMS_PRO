<?php
$patient_name      = isset($patient['patient_name']) ? $patient['patient_name'] : '';
$patient_id        = isset($patient['patient_id']) ? $patient['patient_id'] : (isset($patient['id']) ? $patient['id'] : '');
$case_reference_id = isset($patient['case_reference_id']) ? $patient['case_reference_id'] : '';
$ipd_id            = isset($patient['ipdid']) ? $patient['ipdid'] : (isset($patient['ipd_id']) ? $patient['ipd_id'] : '');
$currency_symbol   = $this->customlib->getHospitalCurrencyFormat();
include(APPPATH . 'views/admin/shared/_print_css.php');
?>

<div class="fixed-print-header">
    <?php if (!empty($print_details[0]['print_header'])) { ?>
        <img src="<?php echo $this->media_storage->getImageURL($print_details[0]['print_header']); ?>"
             class="img-fluid sh-avatar-cover" >
    <?php } ?>
</div>

<table class="table-print-full" width="100%">
    <thead>
        <tr><td><div class="header-space">&nbsp;</div></td></tr>
    </thead>
    <tbody>
        <tr><td>
            <div class="content-body sh-px-12">
            <div class="print-area">

                <!-- ① Document title -->
                <div class="sh-print-title"><?php echo $this->lang->line('payment_receipt'); ?></div>

                <!-- ② Patient / IPD info block -->
                <div class="sh-print-info-block">
                    <table class="sh-print-info-table">
                        <colgroup>
                            <col style="width:14%"><col style="width:36%">
                            <col style="width:16%"><col style="width:34%">
                        </colgroup>
                        <tr>
                            <th><?php echo $this->lang->line('patient'); ?></th>
                            <td><?php echo (($patient_name || $patient_id) ? composePatientName($patient_name, $patient_id) : '-'); ?></td>
                            <th><?php echo $this->lang->line('ipd_no'); ?></th>
                            <td><?php echo ($ipd_id ? $this->customlib->getSessionPrefixByType('ipd_no') . $ipd_id : '-'); ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $this->lang->line('case_id'); ?></th>
                            <td><?php echo ($case_reference_id ?: '-'); ?></td>
                            <th><?php echo $this->lang->line('date'); ?></th>
                            <td><?php echo date($this->customlib->getHospitalDateFormat(true, true)); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Divider for clarity -->
                <div class="sh-section-divider"></div>

                <!-- ③ Payment detail table -->
                <div class="sh-print-section-title"><?php echo $this->lang->line('payment_details'); ?></div>
                <table class="sh-print-table">
                    <thead>
                        <tr>
                            <th style="width:4%">#</th>
                            <th><?php echo $this->lang->line('description'); ?></th>
                            <th style="width:20%; white-space: nowrap;"><?php echo !empty($this->lang->line('date')) ? $this->lang->line('date') : 'Date'; ?></th>
                            <th style="width:16%"><?php echo !empty($this->lang->line('payment_mode')) ? $this->lang->line('payment_mode') : 'Payment Mode'; ?></th>
                            <th class="sh-col-18 sh-text-right text-end" style="text-align: right;"><?php echo $this->lang->line('amount') . ' (' . $currency_symbol . ')'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        $total_payment = 0;
                        $total_refund  = 0;
                        if (!empty($payment_details)) {
                            // Display in ascending order of ID so older transactions appear first, newest last
                            $sorted_payments = $payment_details;
                            usort($sorted_payments, function($a, $b) {
                                return (int)$a['id'] - (int)$b['id'];
                            });

                            foreach ($sorted_payments as $payment) {
                                $is_refund = (isset($payment['type']) && $payment['type'] == 'refund');
                                $amount    = (float)$payment['amount'];
                                if ($is_refund) {
                                    $total_refund += $amount;
                                } else {
                                    $total_payment += $amount;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td>
                                        <strong>
                                            <?php echo $is_refund ? $this->lang->line('payment_refund') : $this->lang->line('payment_received'); ?>
                                            (<?php echo $this->customlib->getSessionPrefixByType('transaction_id') . $payment['id']; ?>)
                                        </strong>
                                        <?php if (!empty($payment['note'])) { ?>
                                            <small><br><?php echo $this->lang->line('note') . ': ' . $payment['note']; ?></small>
                                        <?php } ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <?php
                                        if ($payment['payment_date']) {
                                            $p_date = $this->customlib->YYYYMMDDHisTodateFormat($payment['payment_date'], $this->customlib->getHospitalTimeFormat());
                                            echo strtr($p_date, ['AM' => 'am', 'PM' => 'pm']);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-capitalize">
                                        <?php echo $this->lang->line(strtolower($payment['payment_mode'])); ?>
                                        <?php if ($payment['payment_mode'] == 'Cheque') { ?>
                                            <small>
                                                <?php if (!empty($payment['cheque_no'])) { ?>
                                                    <br><?php echo $this->lang->line('cheque_no') . ': ' . $payment['cheque_no']; ?>
                                                <?php } ?>
                                                <?php if (!empty($payment['cheque_date']) && $payment['cheque_date'] != '0000-00-00') { ?>
                                                    <br><?php echo $this->lang->line('cheque_date') . ': ' . $this->customlib->YYYYMMDDTodateFormat($payment['cheque_date']); ?>
                                                <?php } ?>
                                            </small>
                                        <?php } ?>
                                    </td>
                                    <td class="sh-text-right" style="text-align: right;">
                                        <?php if ($is_refund) { ?>
                                            <span class="text-danger">- <?php echo amountFormat($amount); ?></span>
                                        <?php } else { ?>
                                            <?php echo amountFormat($amount); ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                            </tr>
                            <?php
                        }
                        $net_paid   = $total_payment - $total_refund;
                        $due_amount = isset($total_charge) ? max(0, (float)$total_charge - $net_paid) : 0;
                        ?>
                    </tbody>
                    <tfoot>
                        <?php if ($total_refund > 0) { ?>
                            <tr>
                                <td colspan="4" class="sh-text-right" style="border-top:1px solid #ddd; padding: 4px 8px;"><?php echo $this->lang->line('paid_amount'); ?></td>
                                <td class="sh-text-right" style="border-top:1px solid #ddd; padding: 4px 8px;"><?php echo $currency_symbol . amountFormat($total_payment); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="sh-text-right" style="border:none; padding: 4px 8px;"><?php echo $this->lang->line('refund'); ?></td>
                                <td class="sh-text-right text-danger" style="border:none; padding: 4px 8px;">- <?php echo $currency_symbol . amountFormat($total_refund); ?></td>
                            </tr>
                        <?php } ?>
                        <tr class="sh-row-total">
                            <td colspan="4" class="sh-text-right"><?php echo !empty($this->lang->line('total_paid')) ? $this->lang->line('total_paid') : 'Total Paid'; ?></td>
                            <td class="sh-text-right"><?php echo $currency_symbol . amountFormat($net_paid); ?></td>
                        </tr>
                        <tr class="sh-row-due">
                            <td colspan="4" class="sh-text-right" style="border-top: 1px dashed #cbd5e1; font-weight: 700; font-size: 12px; padding-top: 6px;"><?php echo !empty($this->lang->line('due_amount')) ? $this->lang->line('due_amount') : 'Due Amount'; ?></td>
                            <td class="sh-text-right" style="border-top: 1px dashed #cbd5e1; font-weight: 700; font-size: 12px; padding-top: 6px;"><?php echo $currency_symbol . amountFormat($due_amount); ?></td>
                        </tr>
                    </tfoot>
                </table>

            </div>
            </div>
        </td></tr>
    </tbody>
    <tfoot>
        <tr><td>
            <?php if (!empty($print_details[0]['print_footer'])) { ?>
                <div class="footer-space">&nbsp;</div>
            <?php } ?>
        </td></tr>
    </tfoot>
</table>

<?php if (!empty($print_details[0]['print_footer'])) { ?>
<div class="footer-fixed">
    <?php echo $print_details[0]['print_footer']; ?>
</div>
<?php } ?>
