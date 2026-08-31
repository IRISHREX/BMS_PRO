<?php
$currency_symbol = isset($currency_symbol) ? $currency_symbol : '';
?>
<style>
    @media print {
        @page {
            margin: 8mm 6mm 8mm 6mm;
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
    .txn-print-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 10px;
        color: #111;
        width: 100%;
        max-width: 100%;
        background: #fff;
        padding: 4px 2px;
        box-sizing: border-box !important;
    }
    .txn-header-title {
        text-align: center !important;
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #000 !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 3px !important;
        text-transform: uppercase !important;
    }
    .txn-header-subtitle {
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #222 !important;
        margin-bottom: 12px !important;
    }
    .txn-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        font-size: 10px !important;
        margin: 0 auto !important;
        background: #fff !important;
    }
    .txn-table th,
    .txn-table td {
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        padding: 3px 4px !important;
        line-height: 1.25 !important;
        vertical-align: middle !important;
        font-size: 10px !important;
        color: #000 !important;
    }
    .txn-table th {
        font-weight: 700 !important;
        background-color: #fff !important;
        color: #000 !important;
        text-align: left !important;
    }
    .txn-table th:last-child,
    .txn-table td:last-child {
        border-right: 1px solid #777 !important;
    }

    .txn-table .col-date     { width: 10.5% !important; }
    .txn-table .col-ref      { width: 9.5% !important; }
    .txn-table .col-dept     { width: 11.0% !important; }
    .txn-table .col-patient  { width: 19.0% !important; }
    .txn-table .col-staff    { width: 14.0% !important; }
    .txn-table .col-type     { width: 8.0% !important; }
    .txn-table .col-mode     { width: 8.0% !important; }
    .txn-table .col-refund   { width: 9.5% !important; text-align: right !important; }
    .txn-table .col-amt      { width: 10.5% !important; text-align: right !important; }

    .txn-table td.t-center   { text-align: center !important; }
    .txn-table td.t-left     { text-align: left !important; }
    .txn-table td.t-right    { text-align: right !important; }
    .txn-table td.fw-bold    { font-weight: 700 !important; }
    .txn-table .nowrap       { white-space: nowrap !important; }

    .summary-title {
        text-align: right !important;
        font-weight: 700 !important;
        padding-right: 8px !important;
    }
    .summary-val {
        text-align: right !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
    }
    .summary-highlight {
        background-color: #f2f4f7 !important;
    }
    .summary-row {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .no-data-msg {
        text-align: center;
        padding: 30px;
        font-size: 12px;
        color: #666;
    }
</style>

<div class="txn-print-container">
    <div class="txn-header-title"><?php echo html_escape($hospital_name); ?></div>
    <div class="txn-header-subtitle"><?php echo html_escape($report_subtitle); ?></div>

    <?php if (!empty($print_rows)) { ?>
        <table class="txn-table">
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-ref">Refrence</th>
                    <th class="col-dept">Department</th>
                    <th class="col-patient">Patient Name</th>
                    <th class="col-staff">Collected By</th>
                    <th class="col-type">Payment Type</th>
                    <th class="col-mode">Patient Mode</th>
                    <th class="col-refund">Refund Amount</th>
                    <th class="col-amt">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($print_rows as $row) { ?>
                    <tr>
                        <td class="col-date t-left nowrap"><?php echo html_escape($row['date']); ?></td>
                        <td class="col-ref t-left nowrap"><?php echo html_escape($row['reference']); ?></td>
                        <td class="col-dept t-left"><?php echo html_escape($row['department']); ?></td>
                        <td class="col-patient t-left"><?php echo html_escape($row['patient_name']); ?></td>
                        <td class="col-staff t-left"><?php echo html_escape($row['collected_by']); ?></td>
                        <td class="col-type t-left"><?php echo html_escape($row['payment_type']); ?></td>
                        <td class="col-mode t-left"><?php echo html_escape($row['patient_mode']); ?></td>
                        <td class="col-refund t-right nowrap"><?php echo html_escape($row['refund_amount']); ?></td>
                        <td class="col-amt t-right nowrap"><?php echo html_escape($row['amount']); ?></td>
                    </tr>
                <?php } ?>

                <!-- Summary rows rendered strictly once at the end of the report (last page) -->
                <tr class="summary-row">
                    <td colspan="7" class="summary-title">Net Amount</td>
                    <td colspan="2" class="summary-val"><?php echo number_format($sum_amount, 2); ?></td>
                </tr>
                <tr class="summary-row">
                    <td colspan="7" class="summary-title">Total Refund</td>
                    <td colspan="2" class="summary-val"><?php echo number_format($total_refund, 2); ?></td>
                </tr>
                <tr class="summary-row summary-highlight">
                    <td colspan="7" class="summary-title">Total Income</td>
                    <td colspan="2" class="summary-val"><?php echo number_format($total_income, 2); ?></td>
                </tr>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="no-data-msg">
            No records found
        </div>
    <?php } ?>
</div>
