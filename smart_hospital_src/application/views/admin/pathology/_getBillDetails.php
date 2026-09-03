<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
$amount          = 0;
$tax_amt         = 0;
include(APPPATH . 'views/admin/shared/_print_css.php');

// Pre-calculate totals for status badge
foreach ($result->pathology_report as $rv) {
    $d_amt    = ($rv->apply_charge * $result->discount_percentage) / 100;
    $amount  += $rv->apply_charge;
    $tax_amt += ($rv->tax_percentage > 0) ? (($rv->apply_charge - $d_amt) * $rv->tax_percentage / 100) : 0;
}
$base_net_amt     = ($amount - $result->discount) + $result->tax;
$refund_amt       = (float)($result->refund_amount ?? 0);
$gross_paid       = (float)$result->total_deposit + $refund_amt;
$init_balance     = max(0, $base_net_amt - $gross_paid);
$ref_to_balance   = min($refund_amt, $init_balance);
$ref_to_paid      = max(0, $refund_amt - $ref_to_balance);
$adjusted_net_amt = max(0, $base_net_amt - $refund_amt);
$final_paid_amt   = max(0, $gross_paid - $ref_to_paid);
$total_due        = max(0, $init_balance - $ref_to_balance);

$amount    = 0; // reset for row-level loop below
$tax_amt   = 0;

// Status Badge Calculation
if (isset($result->is_canceled) && $result->is_canceled == 1) {
    $payment_status = $this->lang->line('canceled') ?: 'CANCELLED';
    $status_color   = '#dc2626';
    $status_bg      = '#fef2f2';
    $status_border  = '#f87171';
} elseif (isset($result->bill_status) && in_array($result->bill_status, ['refunded_approved', 'refunded']) && $total_due <= 0 && $final_paid_amt <= 0) {
    $payment_status = $this->lang->line('refunded') ?: 'REFUNDED';
    $status_color   = '#0284c7';
    $status_bg      = '#f0f9ff';
    $status_border  = '#38bdf8';
} elseif ($total_due <= 0 && $adjusted_net_amt > 0) {
    $payment_status = $this->lang->line('paid') ?: 'PAID';
    $status_color   = '#16a34a';
    $status_bg      = '#f0fdf4';
    $status_border  = '#22c55e';
} elseif ($final_paid_amt > 0 && $total_due > 0) {
    $payment_status = $this->lang->line('partial') ?: 'PARTIAL';
    $status_color   = '#d97706';
    $status_bg      = '#fffbeb';
    $status_border  = '#f59e0b';
} else {
    $payment_status = $this->lang->line('unpaid') ?: 'UNPAID';
    $status_color   = '#dc2626';
    $status_bg      = '#fef2f2';
    $status_border  = '#f87171';
}

// Patient Demographics format: (e.g., "Gender: Male / Age: 43y,5m,20d")
$gender_name = !empty($result->gender) ? $this->lang->line(strtolower($result->gender)) : '-';

$compact_age = '';
$p_age   = isset($result->age) ? (int)$result->age : 0;
$p_month = isset($result->month) ? (int)$result->month : 0;
$p_day   = isset($result->day) ? (int)$result->day : 0;
$p_as_of = !empty($result->as_of_date) ? $result->as_of_date : null;

if ($p_as_of != null) {
    try {
        $date1 = new DateTime($p_as_of);
        $today = new DateTime();
        $interval2 = $today->diff($date1);
        $d1 = "P" . $p_age . "Y" . $p_month . "M" . $p_day . "D";
        $interval1 = new DateInterval($d1);
        $totalYears  = $interval1->y + $interval2->y;
        $totalMonths = $interval1->m + $interval2->m;
        $totalDays   = $interval1->d + $interval2->d;
        if ($totalDays >= 30) {
            $totalMonths += floor($totalDays / 30);
            $totalDays    = $totalDays % 30;
        }
        if ($totalMonths >= 12) {
            $totalYears  += floor($totalMonths / 12);
            $totalMonths  = $totalMonths % 12;
        }
        $compact_age = $totalYears . "y," . $totalMonths . "m," . $totalDays . "d";
    } catch (Exception $e) {
        $compact_age = $p_age . "y," . $p_month . "m," . $p_day . "d";
    }
} elseif (!empty($result->dob)) {
    try {
        $dob   = new DateTime($result->dob);
        $today = new DateTime();
        $diff  = $today->diff($dob);
        $compact_age = $diff->y . "y," . $diff->m . "m," . $diff->d . "d";
    } catch (Exception $e) {
        $compact_age = $p_age . "y," . $p_month . "m," . $p_day . "d";
    }
} elseif ($p_age > 0 || $p_month > 0 || $p_day > 0) {
    $compact_age = $p_age . "y," . $p_month . "m," . $p_day . "d";
} else {
    $raw_age = $this->customlib->get_patient_current_age($result->patient_id);
    if (preg_match('/(\d+)\s*[^,\d]+,\s*(\d+)\s*[^,\d]+,\s*(\d+)/i', $raw_age, $m)) {
        $compact_age = $m[1] . "y," . $m[2] . "m," . $m[3] . "d";
    } elseif (preg_match('/(\d+)/', $raw_age, $m)) {
        $compact_age = $m[1] . "y";
    } else {
        $compact_age = '-';
    }
}

$patient_demographics = "Gender: " . $gender_name . " / Age: " . $compact_age;
?>

<style>
@media print {
    @page {
        size: auto;
        margin: 7mm 9mm;
    }
    html, body {
        width: 100%;
        margin: 0;
        padding: 0;
        font-size: 11px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print-area {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .sh-print-info-block {
        page-break-inside: avoid;
        break-inside: avoid;
        padding: 8px 14px 6px !important;
        margin-bottom: 8px !important;
    }
    .sh-receipt-heading-table {
        margin-bottom: 8px !important;
    }
    .sh-print-section-title {
        margin: 8px 0 5px !important;
        padding-bottom: 3px !important;
    }
    .sh-print-table thead th {
        padding: 5px 6px !important;
    }
    .sh-print-table tbody td {
        padding: 3px 6px !important;
        font-size: 9.5px !important;
    }
    .sh-print-table tfoot td {
        padding: 2px 6px !important;
    }
    .sh-print-table tfoot .sh-row-total td {
        padding-top: 5px !important;
        padding-bottom: 3px !important;
    }
}
.sh-receipt-heading-table {
    width: 100%;
    border-top: 2px solid #111;
    border-bottom: 1px solid #111;
    margin-bottom: 10px;
    border-collapse: collapse;
}
.sh-payment-status-badge {
    display: inline-block;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 1px;
    padding: 3px 12px;
    border-radius: 4px;
    line-height: 1.2;
    text-transform: uppercase;
}
.sh-print-info-2col {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.sh-print-info-2col > tbody > tr > td {
    width: 50%;
    vertical-align: top;
}
.sh-print-info-table {
    width: 100%;
    border-collapse: collapse;
}
.sh-print-info-table th {
    font-size: 9.5px;
    font-weight: 600;
    color: #475569;
    padding: 2.5px 0;
    text-align: left !important;
    vertical-align: top;
    white-space: nowrap;
}
.sh-print-info-table th::after {
    content: ' :';
    color: #475569;
    font-weight: 500;
}
.sh-print-info-table td {
    font-size: 10.5px;
    font-weight: 700;
    color: #0f172a;
    padding: 2.5px 0 2.5px 6px;
    text-align: left !important;
    vertical-align: top;
    word-break: break-word;
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
        <tr><td><div class="header-space">&nbsp;</div></td></tr>
    </thead>
    <tbody>
        <tr><td>
            <div class="content-body sh-px-12" >
            <div class="print-area">

                <!-- Receipt Heading with Date on Top-Left, Centered Title, and Payment Status on Top-Right -->
                <table class="sh-receipt-heading-table">
                    <tr>
                        <td style="width:28%; text-align:left; vertical-align:middle; font-size:11px; font-weight:600; color:#1e293b; white-space:nowrap;">
                            <span style="color:#64748b; font-weight:500;"><?php echo $this->lang->line('date'); ?>:</span>
                            <strong><?php echo $this->customlib->YYYYMMDDHisTodateFormat($result->date, $this->customlib->getHospitalTimeFormat()); ?></strong>
                        </td>
                        <td style="width:44%; text-align:center; vertical-align:middle; padding:6px 0; font-size:13px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#111;">
                            <?php echo $this->lang->line('pathology_single_billing'); ?>
                        </td>
                        <td style="width:28%; text-align:right; vertical-align:middle; padding-right:2px;">
                            <span class="sh-payment-status-badge" style="color:<?php echo $status_color; ?>; background:<?php echo $status_bg; ?>; border:1.5px solid <?php echo $status_border; ?>;">
                                <?php echo $payment_status; ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <!-- 2-Column Patient Details Box -->
                <div class="sh-print-info-block">
                    <table class="sh-print-info-2col">
                        <tbody>
                            <tr>
                                <!-- Column 1: Bill & Patient Details -->
                                <td style="width:50%; vertical-align:top; padding-right:12px;">
                                    <table class="sh-print-info-table">
                                        <colgroup><col style="width:38%"><col style="width:62%"></colgroup>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('bill_no'); ?></th>
                                            <td style="text-align:left;"><?php echo ($bill_prefix . $result->id); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('patient_name'); ?></th>
                                            <td style="text-align:left;"><?php echo ($result->patient_name ?: '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; font-size:10.5px; color:#0f172a; padding:2.5px 0; white-space:nowrap;">
                                                <span style="font-weight:normal; color:#475569;">Gender:</span> <strong style="font-weight:700; color:#0f172a;"><?php echo $gender_name; ?></strong>
                                                <span style="color:#94a3b8; margin:0 4px;">/</span>
                                                <span style="font-weight:normal; color:#475569;">Age:</span> <strong style="font-weight:700; color:#0f172a;"><?php echo $compact_age; ?></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <!-- Column 2: Clinical Reference & Contact -->
                                <td style="width:50%; vertical-align:top; padding-left:12px;">
                                    <table class="sh-print-info-table">
                                        <colgroup><col style="width:40%"><col style="width:60%"></colgroup>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('consultant_doctor'); ?></th>
                                            <td style="text-align:left;"><?php echo (preg_replace('/\s*\([^)]*\)$/', '', $result->doctor_name) ?: '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('referred_by'); ?></th>
                                            <td style="text-align:left;"><?php echo ($result->referral_person_name ?: '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('phone'); ?></th>
                                            <td style="text-align:left;"><?php echo ($result->mobileno ?: '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('address'); ?></th>
                                            <td style="text-align:left;"><?php echo ($result->address ?: '-'); ?></td>
                                        </tr>
                                        <?php if ($result->case_reference_id > 0) { ?>
                                        <tr>
                                            <th style="text-align:left;"><?php echo $this->lang->line('case_id'); ?></th>
                                            <td style="text-align:left;"><?php echo $result->case_reference_id; ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if (!empty($fields)) { foreach ($fields as $fields_key => $fields_value) {
                                            $display_field = $result->{"$fields_value->name"} ?? '';
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
                                            <td><?php echo $display_field; ?></td>
                                        </tr>
                                        <?php } } ?>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="sh-section-divider"></div>

                <div class="sh-print-section-title"><?php echo $this->lang->line('payment_details'); ?></div>
                <table class="sh-print-table">
                    <thead>
                        <tr>
                            <th style="width:4%">#</th>
                            <th style="width:54%"><?php echo $this->lang->line('test_name'); ?></th>
                            <th style="width:16%" class="text-end"><?php echo $this->lang->line('report_date'); ?></th>
                            <th style="width:11%" class="text-end"><?php echo $this->lang->line('tax'); ?></th>
                            <th style="width:15%" class="sh-text-right"><?php echo $this->lang->line('amount') . ' (' . $currency_symbol . ')'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $row_counter = 1;
                        foreach ($result->pathology_report as $report_key => $report_value) {
                            $discount_amt  = ($report_value->apply_charge * $result->discount_percentage) / 100;
                            $amount       += $report_value->apply_charge;
                            $tax           = ($report_value->tax_percentage > 0)
                                             ? (($report_value->apply_charge - $discount_amt) * $report_value->tax_percentage / 100)
                                             : 0;
                            $tax_amt      += $tax;
                        ?>
                        <tr>
                            <td style="font-size:9.5px;"><?php echo $row_counter++; ?></td>
                            <td class="fw-bold" style="font-size:9.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?php echo ($report_value->test_name ?: '-'); ?>
                                <?php if (!empty($report_value->short_name)) { ?>
                                    <span class="fw-medium text-muted" style="font-size:8.5px; font-weight:normal; color:#64748b; margin-left:4px;">(<?php echo html_escape($report_value->short_name); ?>)</span>
                                <?php } ?>
                            </td>
                            <td class="sh-text-right" style="font-size:9.5px; white-space:nowrap;"><?php echo (!empty($report_value->reporting_date) ? $this->customlib->YYYYMMDDTodateFormat($report_value->reporting_date) : '-'); ?></td>
                            <td class="sh-text-right" style="font-size:9.5px; white-space:nowrap;">
                                <?php echo ($report_value->tax_percentage > 0) ? (amountFormat($tax) . ' (' . $report_value->tax_percentage . '%)') : '-'; ?>
                            </td>
                            <td class="sh-text-right" style="font-size:9.5px; white-space:nowrap;"><?php echo amountFormat($report_value->apply_charge - $discount_amt + $tax); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="sh-row-first">
                            <td colspan="4"><?php echo $this->lang->line('subtotal'); ?></td>
                            <td><?php echo $currency_symbol . amountFormat($amount); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"><?php echo $this->lang->line('discount'); ?></td>
                            <td><?php
                                $discount_per = ($amount > 0) ? ($result->discount * 100) / $amount : 0;
                                echo $currency_symbol . amountFormat($result->discount) . ($discount_per > 0 ? (' (' . number_format((float)$discount_per, 2, '.', '') . '%)') : '');
                            ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"><?php echo $this->lang->line('tax'); ?></td>
                            <td><?php
                                $denominator = $amount - $result->discount;
                                $tax_per     = ($denominator > 0) ? ($result->tax * 100 / $denominator) : 0;
                                echo $currency_symbol . amountFormat($result->tax) . ($tax_per > 0 ? (' (' . amountFormat($tax_per) . '%)') : '');
                            ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"><?php echo $this->lang->line('net_amount'); ?></td>
                            <td><?php echo $currency_symbol . amountFormat($base_net_amt); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"><?php echo $this->lang->line('paid_amount'); ?></td>
                            <td><?php echo $currency_symbol . amountFormat($final_paid_amt); ?></td>
                        </tr>
                        <?php if (isset($result->refund_amount) && $result->refund_amount > 0) { ?>
                        <tr>
                            <td colspan="4"><?php echo $this->lang->line('refund_amount'); ?></td>
                            <td><?php echo $currency_symbol . amountFormat($result->refund_amount); ?></td>
                        </tr>
                        <?php } ?>
                        <tr class="sh-row-total">
                            <td colspan="4"><?php echo $this->lang->line('due_amount') ?: 'Due Amount'; ?></td>
                            <td><?php echo $currency_symbol . amountFormat($total_due); ?></td>
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
