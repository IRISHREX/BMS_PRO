<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
$logged_in_user = $this->customlib->getAdminSessionUserName();
$printed_date = date('d-m-y h:i:sA');

// Extract details from $result
$ipd_no_str = $this->customlib->getSessionPrefixByType('ipd_no') . $result['ipdid'];
$case_ref_no = !empty($result['case_reference_id']) ? $result['case_reference_id'] : $ipd_no_str;
$admission_date = !empty($result['date']) ? date('d-M-Y h:i:s A', strtotime($result['date'])) : '';

$discharge_date = '-';
if (!empty($result['discharge_date']) && $result['discharge_date'] != '0000-00-00 00:00:00' && $result['discharge_date'] != '0000-00-00') {
    $discharge_date = (strpos($result['discharge_date'], ':') !== false) ? date('d-M-Y h:i:s A', strtotime($result['discharge_date'])) : date('d-M-Y', strtotime($result['discharge_date']));
} else {
    $discharge_date = '-';
}

$reg_no = !empty($result['patient_unique_id']) ? $result['patient_unique_id'] : $result['patient_id'];
$clean_patient_name = preg_replace('/\s*\([^)]*\)$/', '', $result['patient_name']);

$age = $this->customlib->get_patient_current_age($result['patient_id']);
$gender = !empty($result['gender']) ? ucfirst($result['gender']) : '';
$address = !empty($result['address']) ? $result['address'] : '';
$religion = !empty($result['religion']) ? $result['religion'] : '';
$marital_status = !empty($result['marital_status']) ? ucfirst($result['marital_status']) : '';
$phone = !empty($result['mobileno']) ? $result['mobileno'] : '';
$occupation = !empty($result['occupation']) ? $result['occupation'] : '';
$nationality = !empty($result['nationality']) ? $result['nationality'] : 'INDIAN';
$doctor_name = !empty($result['name']) ? 'DR . ' . strtoupper(trim($result['name'] . ' ' . $result['surname'])) : '';
$case_type = !empty($result['case_type']) ? $result['case_type'] : '';
$admitted_by_str = !empty($admitted_by) ? $admitted_by : '-';
$discharged_by_str = !empty($discharged_by) ? $discharged_by : '-';

$hosp_name = isset($hospital_setting[0]['name']) ? $hospital_setting[0]['name'] : '';
$hosp_address = isset($hospital_setting[0]['address']) ? $hospital_setting[0]['address'] : '';
$hosp_phone = isset($hospital_setting[0]['phone']) ? $hospital_setting[0]['phone'] : '';
$hosp_email = isset($hospital_setting[0]['email']) ? $hospital_setting[0]['email'] : '';

// Calculate bill summary metrics
$total_bill = 0;
$total_discount = 0;
if (!empty($charges)) {
    foreach ($charges as $ch) {
        $amt = isset($ch['apply_charge']) ? (float)$ch['apply_charge'] : (isset($ch['amount']) ? (float)$ch['amount'] : 0);
        $dis = isset($ch['discount']) ? (float)$ch['discount'] : 0;
        $total_bill += $amt;
        $total_discount += $dis;
    }
}

$total_paid = 0;
$total_refund = 0;
if (!empty($paymentDetails)) {
    foreach ($paymentDetails as $p) {
        $amt = (float)$p['amount'];
        if (isset($p['type']) && $p['type'] == 'refund') {
            $total_refund += $amt;
        } else {
            $total_paid += $amt;
        }
    }
}

$net_amount = $total_bill - $total_discount;
$net_paid = $total_paid - $total_refund;
$due_amount = $net_amount - $net_paid;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Final Bill - IPD</title>
    <style>
        @media print {
            @page {
                margin: 8mm 6mm 8mm 6mm;
            }
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .page-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Hospital Header */
        .hosp-header {
            text-align: center;
            position: relative;
            margin-bottom: 8px;
        }
        .hosp-header h1 {
            font-size: 24px;
            font-weight: 800;
            color: #1a365d;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .hosp-header p {
            margin: 2px 0;
            font-size: 11px;
            font-weight: bold;
            color: #2b6cb0;
        }
        .hosp-header .logo-left {
            position: absolute;
            left: 0;
            top: 0;
            height: 65px;
        }
        .hosp-header .logo-right {
            position: absolute;
            right: 0;
            top: 0;
            height: 65px;
        }

        /* Main Document Title */
        .doc-main-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Top Info Box (4 Columns) */
        .top-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
            background-color: #f7fafc;
        }
        .top-info-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 10.5px;
            font-weight: bold;
            vertical-align: middle;
        }

        /* Patient Grid Table */
        .patient-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }
        .patient-grid-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10.5px;
            vertical-align: top;
        }
        .patient-grid-table .lbl {
            font-weight: bold;
            width: 18%;
        }
        .patient-grid-table .val {
            width: 32%;
        }

        /* Date Bar */
        .date-bar-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
            margin-bottom: 12px;
        }
        .date-bar-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10.5px;
            font-weight: bold;
        }

        /* Bill Summary Section */
        .summary-header {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            padding: 5px;
            border: 1px solid #000;
            border-bottom: none;
            background-color: #fff;
        }
        .bill-details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bill-details-table th {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10.5px;
            font-weight: bold;
            text-align: left;
        }
        .bill-details-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: none;
            padding: 4px 8px;
            font-size: 10.5px;
        }
        .bill-details-table tr.item-row:last-child td {
            border-bottom: 1px solid #000;
        }

        /* Final Calculations Table */
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }
        .calc-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 10.5px;
        }
        .calc-table .calc-lbl {
            font-weight: bold;
            text-align: right;
            width: 25%;
        }
        .calc-table .calc-val {
            font-weight: bold;
            text-align: right;
            width: 25%;
        }
        .calc-table .highlight-lbl {
            font-weight: bold;
            font-style: italic;
            text-align: right;
        }
        .calc-table .highlight-val {
            font-weight: bold;
            font-style: italic;
            text-align: right;
        }

        /* Signature Row */
        .sig-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 60px;
            margin-bottom: 12px;
            font-size: 10.5px;
            font-weight: bold;
        }
        .sig-box {
            border-top: 1px solid #000;
            padding-top: 4px;
            width: 240px;
            text-align: center;
        }

        /* Footer */
        .print-footer {
            padding-top: 4px;
            margin-top: 40px;
            font-size: 9.5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page-container">

    <!-- Header -->
    <?php if (!empty($print_details[0]['print_header'])) { ?>
        <div style="width: 100%; margin-bottom: 8px;">
            <img src="<?php echo $this->media_storage->getImageURL($print_details[0]['print_header']); ?>" style="width: 100%; height: auto; display: block;">
        </div>
    <?php } else { ?>
        <div class="hosp-header">
            <?php if (!empty($hospital_setting[0]['mini_logo'])): ?>
                <img class="logo-left" src="<?php echo base_url('uploads/hospital_content/logo/' . $hospital_setting[0]['mini_logo']); ?>" alt="Logo">
                <img class="logo-right" src="<?php echo base_url('uploads/hospital_content/logo/' . $hospital_setting[0]['mini_logo']); ?>" alt="Logo">
            <?php endif; ?>
            <h1><?php echo html_escape($hosp_name); ?></h1>
            <p><?php echo html_escape($hosp_address); ?></p>
            <p>Mobile : <?php echo html_escape($hosp_phone); ?> &nbsp;&nbsp;&nbsp;&nbsp; E-mail : <?php echo html_escape($hosp_email); ?></p>
        </div>
    <?php } ?>

    <!-- Document Main Title -->
    <div class="doc-main-title">FINAL BILL</div>

    <!-- Top 4-Column Box -->
    <table class="top-info-table">
        <tr>
            <td style="width: 32%;">ADMISSION FORM: IPD / <?php echo date('Y', strtotime($result['date'])); ?> / <?php echo html_escape($result['ipdid']); ?></td>
            <td style="width: 18%;">Reg. No: <?php echo html_escape($reg_no); ?></td>
            <td style="width: 26%;">Case: <?php echo html_escape(strtoupper($case_type)); ?></td>
            <td style="width: 24%;">Case Reference: <?php echo html_escape($case_ref_no); ?></td>
        </tr>
    </table>

    <!-- Patient Details Grid -->
    <table class="patient-grid-table">
        <tr>
            <td class="lbl">Reg. No</td>
            <td class="val"><?php echo html_escape($reg_no); ?></td>
            <td class="lbl">Doctor</td>
            <td class="val"><strong><?php echo html_escape($doctor_name); ?></strong></td>
        </tr>
        <tr>
            <td class="lbl">Patient Name</td>
            <td class="val"><strong><?php echo html_escape(strtoupper($clean_patient_name)); ?></strong></td>
            <td class="lbl">Age / Gender</td>
            <td class="val"><?php echo html_escape($age); ?> / <?php echo html_escape($gender); ?></td>
        </tr>
        <tr>
            <td class="lbl">Address</td>
            <td class="val"><?php echo html_escape($address); ?></td>
            <td class="lbl">Phone No</td>
            <td class="val"><?php echo html_escape($phone); ?></td>
        </tr>
        <tr>
            <td class="lbl">Religion</td>
            <td class="val"><?php echo html_escape(strtoupper($religion)); ?></td>
            <td class="lbl">Marital Status</td>
            <td class="val"><?php echo html_escape($marital_status); ?></td>
        </tr>
        <tr>
            <td class="lbl">Occupation</td>
            <td class="val"><?php echo html_escape(strtoupper($occupation)); ?></td>
            <td class="lbl">Nationality</td>
            <td class="val"><?php echo html_escape($nationality); ?></td>
        </tr>
    </table>

    <!-- Admission & Discharge Date Bar -->
    <table class="date-bar-table">
        <tr>
            <td style="width: 50%;">ADMISSION DATE : <?php echo html_escape($admission_date); ?></td>
            <td style="width: 50%;">DISCHARGE DATE : <?php echo html_escape($discharge_date); ?></td>
        </tr>
        <tr>
            <td style="width: 50%;">ADMITTED BY : <?php echo html_escape($admitted_by_str); ?></td>
            <td style="width: 50%;">DISCHARGED BY : <?php echo html_escape($discharged_by_str); ?></td>
        </tr>
    </table>

    <!-- Bill Summary Table -->
    <div class="summary-header">BILL SUMMARY</div>
    <table class="bill-details-table">
        <thead>
            <tr>
                <th style="width: 20%;">Date</th>
                <th style="width: 55%;">Charge / Transaction Details</th>
                <th style="width: 25%; text-align: right;">Amount (<?php echo $currency_symbol; ?>)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($charges)): ?>
                <?php foreach ($charges as $c): ?>
                    <tr class="item-row">
                        <td><?php echo !empty($c['date']) ? date('d-M-Y', strtotime($c['date'])) : ''; ?></td>
                        <td><?php echo html_escape(isset($c['name']) ? $c['name'] : (isset($c['charge_category_name']) ? $c['charge_category_name'] : '')); ?></td>
                        <td style="text-align: right;"><?php echo number_format(isset($c['apply_charge']) ? (float)$c['apply_charge'] : (isset($c['amount']) ? (float)$c['amount'] : 0), 2, '.', ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="item-row">
                    <td>-</td>
                    <td>No charges added</td>
                    <td style="text-align: right;">0.00</td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($paymentDetails)): ?>
                <tr class="item-row" style="background-color: #f7fafc; font-weight: bold;">
                    <td colspan="3" style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px 8px;">Payments &amp; Refunds</td>
                </tr>
                <?php foreach ($paymentDetails as $p): ?>
                    <?php 
                        $is_ref = (isset($p['type']) && $p['type'] == 'refund');
                        $tr_id = $this->customlib->getSessionPrefixByType('transaction_id') . $p['id'];
                        $mode = !empty($p['payment_mode']) ? ucfirst($p['payment_mode']) : '';
                        $pdate = !empty($p['payment_date']) ? $this->customlib->YYYYMMDDHisTodateFormat($p['payment_date'], $this->customlib->getHospitalTimeFormat()) : '';
                        $label = $is_ref ? "Refund (" . $tr_id . " - " . $mode . ")" : "Payment Received (" . $tr_id . " - " . $mode . ")";
                        if (!empty($p['note'])) { $label .= " - " . $p['note']; }
                    ?>
                    <tr class="item-row">
                        <td><?php echo html_escape($pdate); ?></td>
                        <td><?php echo html_escape($label); ?></td>
                        <td style="text-align: right; <?php if ($is_ref) { echo 'color: #c53030;'; } ?>">
                            <?php echo ($is_ref ? '- ' : '') . number_format((float)$p['amount'], 2, '.', ''); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Final Calculations -->
    <table class="calc-table">
        <tr>
            <td style="width: 50%; border-right: none;" rowspan="6"></td>
            <td class="calc-lbl" style="width: 25%;">Total Bill Amount</td>
            <td class="calc-val" style="width: 25%;"><?php echo $currency_symbol; ?> <?php echo number_format($total_bill, 2, '.', ''); ?></td>
        </tr>
        <tr>
            <td class="calc-lbl">(Less) dct</td>
            <td class="calc-val"><?php echo $currency_symbol; ?> <?php echo number_format($total_discount, 2, '.', ''); ?></td>
        </tr>
        <tr>
            <td class="calc-lbl highlight-lbl">NET AMOUNT</td>
            <td class="calc-val highlight-val"><?php echo $currency_symbol; ?> <?php echo number_format($net_amount, 2, '.', ''); ?></td>
        </tr>
        <tr>
            <td class="calc-lbl highlight-lbl">Amount Received</td>
            <td class="calc-val highlight-val"><?php echo $currency_symbol; ?> <?php echo number_format($total_paid, 2, '.', ''); ?></td>
        </tr>
        <tr>
            <td class="calc-lbl highlight-lbl">Total Refund</td>
            <td class="calc-val highlight-val"><?php echo $currency_symbol; ?> <?php echo number_format($total_refund, 2, '.', ''); ?></td>
        </tr>
        <tr>
            <td class="calc-lbl highlight-lbl">DUE AMOUNT</td>
            <td class="calc-val highlight-val"><?php echo $currency_symbol; ?> <?php echo number_format($due_amount, 2, '.', ''); ?></td>
        </tr>
    </table>

    <!-- Signature Row -->
    <div class="sig-row">
        <div class="sig-box">Signature of Patient Party</div>
        <div class="sig-box">Signature of Cashier</div>
    </div>

    <!-- Print Footer -->
    <div class="print-footer">
        Printed by : <?php echo html_escape($logged_in_user . '@' . $printed_date); ?>
    </div>

</div>
</body>
</html>
