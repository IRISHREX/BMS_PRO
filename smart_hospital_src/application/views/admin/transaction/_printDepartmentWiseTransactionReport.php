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
    .dwtr-print-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 10px;
        color: #111;
        width: 100%;
        max-width: 100%;
        background: #fff;
        padding: 4px 2px;
        box-sizing: border-box !important;
    }
    .dwtr-header-title {
        text-align: center !important;
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #000 !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 3px !important;
        text-transform: uppercase !important;
    }
    .dwtr-header-subtitle {
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #222 !important;
        margin-bottom: 12px !important;
    }
    .dwtr-table {
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
    .dwtr-table th,
    .dwtr-table td {
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        padding: 3.5px 5px !important;
        line-height: 1.25 !important;
        vertical-align: middle !important;
        font-size: 10px !important;
        color: #000 !important;
    }
    .dwtr-table th {
        font-weight: 700 !important;
        background-color: #fff !important;
        color: #000 !important;
        text-align: left !important;
    }
    .dwtr-table th:last-child,
    .dwtr-table td:last-child {
        border-right: 1px solid #777 !important;
    }

    .dwtr-table .col-date    { width: 12.0% !important; }
    .dwtr-table .col-id      { width: 12.0% !important; }
    .dwtr-table .col-dept    { width: 14.0% !important; }
    .dwtr-table .col-patient { width: 22.0% !important; }
    .dwtr-table .col-ref     { width: 14.0% !important; }
    .dwtr-table .col-mode    { width: 12.0% !important; }
    .dwtr-table .col-amt     { width: 14.0% !important; text-align: right !important; }

    .dwtr-table td.t-center { text-align: center !important; }
    .dwtr-table td.t-left   { text-align: left !important; }
    .dwtr-table td.t-right  { text-align: right !important; }
    .dwtr-table td.fw-bold  { font-weight: 700 !important; }
    .dwtr-table .nowrap     { white-space: nowrap !important; }

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
    .summary-row {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        background-color: #fcfcfc !important;
    }

    .negative-amt {
        color: #c00 !important;
    }

    .no-data-msg {
        text-align: center;
        padding: 30px;
        font-size: 12px;
        color: #666;
    }
</style>

<div class="dwtr-print-container">
    <div class="dwtr-header-title"><?php echo html_escape($hospital_name); ?></div>
    <div class="dwtr-header-subtitle"><?php echo html_escape($report_subtitle); ?></div>

    <?php if (!empty($print_rows)) { ?>
        <table class="dwtr-table">
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-id">Transaction ID</th>
                    <th class="col-dept">Department</th>
                    <th class="col-patient">Patient Name</th>
                    <th class="col-ref">Reference No</th>
                    <th class="col-mode">Payment Mode</th>
                    <th class="col-amt">Amount (<?php echo $currency_symbol; ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($print_rows as $row) { ?>
                    <tr>
                        <td class="col-date t-left nowrap"><?php echo html_escape($row['date']); ?></td>
                        <td class="col-id t-left nowrap"><?php echo html_escape($row['transaction_id']); ?></td>
                        <td class="col-dept t-left"><?php echo html_escape($row['department']); ?></td>
                        <td class="col-patient t-left"><?php echo html_escape($row['patient_name']); ?></td>
                        <td class="col-ref t-left nowrap"><?php echo html_escape($row['reference_no']); ?></td>
                        <td class="col-mode t-left nowrap"><?php echo html_escape($row['payment_mode']); ?></td>
                        <td class="col-amt t-right nowrap <?php echo (!empty($row['is_refund'])) ? 'negative-amt' : ''; ?>">
                            <?php echo html_escape($row['amount']); ?>
                        </td>
                    </tr>
                <?php } ?>

                <!-- Summary rows rendered strictly once at the end of the report (last page only) -->
                <tr class="summary-row">
                    <td colspan="6" class="summary-title">Total Amount</td>
                    <td class="summary-val">
                        <?php echo number_format($total_amount ?? 0, 2); ?>
                    </td>
                </tr>
                <tr class="summary-row">
                    <td colspan="6" class="summary-title">Total Refund</td>
                    <td class="summary-val <?php echo (isset($total_refund) && $total_refund > 0) ? 'negative-amt' : ''; ?>">
                        <?php echo (isset($total_refund) && $total_refund > 0 ? '-' : '') . number_format($total_refund ?? 0, 2); ?>
                    </td>
                </tr>
                <tr class="summary-row">
                    <td colspan="6" class="summary-title">Net Amount</td>
                    <td class="summary-val <?php echo (isset($net_amount) && $net_amount < 0) ? 'negative-amt' : ''; ?>">
                        <?php echo number_format($net_amount ?? 0, 2); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="no-data-msg">
            No records found
        </div>
    <?php } ?>
</div>
