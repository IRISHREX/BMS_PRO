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
    .exp-print-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 10px;
        color: #111;
        width: 100%;
        max-width: 100%;
        background: #fff;
        padding: 4px 2px;
        box-sizing: border-box !important;
    }
    .exp-header-title {
        text-align: center !important;
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #000 !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 3px !important;
        text-transform: uppercase !important;
    }
    .exp-header-subtitle {
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #222 !important;
        margin-bottom: 12px !important;
    }
    .exp-table {
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
    .exp-table th,
    .exp-table td {
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        padding: 3.5px 5px !important;
        line-height: 1.25 !important;
        vertical-align: middle !important;
        font-size: 10px !important;
        color: #000 !important;
    }
    .exp-table th {
        font-weight: 700 !important;
        background-color: #fff !important;
        color: #000 !important;
        text-align: left !important;
    }
    .exp-table th:last-child,
    .exp-table td:last-child {
        border-right: 1px solid #777 !important;
    }

    .exp-table .col-name { width: 25.0% !important; }
    .exp-table .col-inv  { width: 20.0% !important; }
    .exp-table .col-head { width: 20.0% !important; }
    .exp-table .col-date { width: 18.0% !important; }
    .exp-table .col-amt  { width: 17.0% !important; text-align: right !important; }

    .exp-table td.t-center { text-align: center !important; }
    .exp-table td.t-left   { text-align: left !important; }
    .exp-table td.t-right  { text-align: right !important; }
    .exp-table td.fw-bold  { font-weight: 700 !important; }
    .exp-table .nowrap     { white-space: nowrap !important; }

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

    .no-data-msg {
        text-align: center;
        padding: 30px;
        font-size: 12px;
        color: #666;
    }
</style>

<div class="exp-print-container">
    <div class="exp-header-title"><?php echo html_escape($hospital_name); ?></div>
    <div class="exp-header-subtitle"><?php echo html_escape($report_subtitle); ?></div>

    <?php if (!empty($print_rows)) { ?>
        <table class="exp-table">
            <thead>
                <tr>
                    <th class="col-name">Name</th>
                    <th class="col-inv">Invoice Number</th>
                    <th class="col-head">Expense Head</th>
                    <th class="col-date">Date</th>
                    <th class="col-amt">Amount (<?php echo $currency_symbol; ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($print_rows as $row) { ?>
                    <tr>
                        <td class="col-name t-left"><?php echo html_escape($row['name']); ?></td>
                        <td class="col-inv t-left nowrap"><?php echo html_escape($row['invoice_no']); ?></td>
                        <td class="col-head t-left"><?php echo html_escape($row['expense_head']); ?></td>
                        <td class="col-date t-left nowrap"><?php echo html_escape($row['date']); ?></td>
                        <td class="col-amt t-right nowrap"><?php echo html_escape($row['amount']); ?></td>
                    </tr>
                <?php } ?>

                <!-- Totals row rendered strictly once at the end of the report (last page only) -->
                <tr class="summary-row">
                    <td colspan="4" class="summary-title">Total Amount</td>
                    <td class="summary-val">
                        <?php echo number_format($total_amount, 2); ?>
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
