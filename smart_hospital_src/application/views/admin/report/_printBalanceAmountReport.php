<?php
$currency_symbol = isset($currency_symbol) ? $currency_symbol : '';
?>
<style>
    @media print {
        @page {
            margin: 8mm 5mm 8mm 5mm;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            box-sizing: border-box !important;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif !important;
            overflow-x: hidden !important;
        }
    }
    *, *:before, *:after {
        box-sizing: border-box !important;
    }
    .bal-print-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 9.5px;
        color: #111;
        width: 100%;
        max-width: 100%;
        background: #fff;
        padding: 4px 2px;
        box-sizing: border-box !important;
    }
    .bal-header-title {
        text-align: center !important;
        font-size: 16px !important;
        font-weight: 800 !important;
        color: #000 !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 3px !important;
        text-transform: uppercase !important;
    }
    .bal-header-subtitle {
        text-align: center !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        color: #222 !important;
        margin-bottom: 10px !important;
    }
    .bal-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        font-size: 9px !important;
        margin: 0 auto !important;
        background: #fff !important;
    }
    .bal-table th,
    .bal-table td {
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        padding: 2.5px 2.5px !important;
        line-height: 1.25 !important;
        vertical-align: middle !important;
        font-size: 9px !important;
        color: #000 !important;
    }
    .bal-table th {
        font-weight: 700 !important;
        background-color: #fff !important;
        color: #000 !important;
        text-align: left !important;
    }
    .bal-table th:last-child,
    .bal-table td:last-child {
        border-right: 1px solid #777 !important;
    }

    .bal-table .col-bill      { width: 7.5% !important; }
    .bal-table .col-case      { width: 5.5% !important; }
    .bal-table .col-patient   { width: 14.0% !important; }
    .bal-table .col-staff     { width: 12.0% !important; }
    .bal-table .col-doctor    { width: 12.0% !important; }
    .bal-table .col-amt       { width: 7.5% !important; text-align: right !important; }
    .bal-table .col-discount  { width: 7.0% !important; text-align: right !important; }
    .bal-table .col-tax       { width: 6.5% !important; text-align: right !important; }
    .bal-table .col-net       { width: 7.5% !important; text-align: right !important; }
    .bal-table .col-paid      { width: 7.0% !important; text-align: right !important; }
    .bal-table .col-refund    { width: 6.5% !important; text-align: right !important; }
    .bal-table .col-balance   { width: 7.0% !important; text-align: right !important; }

    .bal-table td.t-center    { text-align: center !important; }
    .bal-table td.t-left      { text-align: left !important; }
    .bal-table td.t-right     { text-align: right !important; }
    .bal-table td.fw-bold     { font-weight: 700 !important; }
    .bal-table .nowrap        { white-space: nowrap !important; }

    .summary-title {
        text-align: right !important;
        font-weight: 700 !important;
        padding-right: 4px !important;
    }
    .summary-val {
        text-align: right !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
    }
    .summary-row {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        background-color: #fcfcfc !important;
    }
    .summary-highlight {
        background-color: #f2f4f7 !important;
    }

    .no-data-msg {
        text-align: center;
        padding: 30px;
        font-size: 12px;
        color: #666;
    }
</style>

<div class="bal-print-container">
    <div class="bal-header-title"><?php echo html_escape($hospital_name); ?></div>
    <div class="bal-header-subtitle"><?php echo html_escape($report_subtitle); ?></div>

    <?php if (!empty($print_rows)) { ?>
        <table class="bal-table">
            <thead>
                <tr>
                    <th class="col-bill">Bill No</th>
                    <th class="col-case">Case ID</th>
                    <th class="col-patient">Patient Name</th>
                    <th class="col-staff">Generated By</th>
                    <th class="col-doctor">Reference Doctor</th>
                    <th class="col-amt">Amount (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-discount">Discount (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-tax">Tax (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-net">Net Amount (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-paid">Paid Amount (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-refund">Refund Amount (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-balance">Balance Amount (<?php echo $currency_symbol; ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($print_rows as $row) { ?>
                    <tr>
                        <td class="col-bill t-left nowrap"><?php echo html_escape($row['bill_no']); ?></td>
                        <td class="col-case t-left"><?php echo html_escape($row['case_id']); ?></td>
                        <td class="col-patient t-left"><?php echo html_escape($row['patient_name']); ?></td>
                        <td class="col-staff t-left"><?php echo html_escape($row['generated_by']); ?></td>
                        <td class="col-doctor t-left"><?php echo html_escape($row['doctor_name']); ?></td>
                        <td class="col-amt t-right nowrap"><?php echo html_escape($row['amount']); ?></td>
                        <td class="col-discount t-right nowrap"><?php echo html_escape($row['discount']); ?></td>
                        <td class="col-tax t-right nowrap"><?php echo html_escape($row['tax']); ?></td>
                        <td class="col-net t-right nowrap"><?php echo html_escape($row['net_amount']); ?></td>
                        <td class="col-paid t-right nowrap"><?php echo html_escape($row['paid_amount']); ?></td>
                        <td class="col-refund t-right nowrap"><?php echo html_escape($row['refund_amount']); ?></td>
                        <td class="col-balance t-right nowrap"><?php echo html_escape($row['balance_amount']); ?></td>
                    </tr>
                <?php } ?>

                <!-- Totals rendered once at the end of the report (last page only) -->
                <tr class="summary-row">
                    <td colspan="5" class="summary-title">Total Amount</td>
                    <td class="summary-val"><?php echo number_format($total_amount, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_discount, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_tax, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_net_amount, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_paid_amount, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_refund_amount, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_balance_amount, 2); ?></td>
                </tr>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="no-data-msg">
            No records found
        </div>
    <?php } ?>
</div>
