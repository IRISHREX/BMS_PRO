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
    .dtr-print-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 10px;
        color: #111;
        width: 100%;
        max-width: 100%;
        background: #fff;
        padding: 4px 2px;
        box-sizing: border-box !important;
    }
    .dtr-header-title {
        text-align: center !important;
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #000 !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 3px !important;
        text-transform: uppercase !important;
    }
    .dtr-header-subtitle {
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #222 !important;
        margin-bottom: 12px !important;
    }
    .dtr-table {
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
    .dtr-table th,
    .dtr-table td {
        box-sizing: border-box !important;
        border: 1px solid #777 !important;
        padding: 3.5px 5px !important;
        line-height: 1.25 !important;
        vertical-align: middle !important;
        font-size: 10px !important;
        color: #000 !important;
    }
    .dtr-table th {
        font-weight: 700 !important;
        background-color: #fff !important;
        color: #000 !important;
        text-align: left !important;
    }
    .dtr-table th:last-child,
    .dtr-table td:last-child {
        border-right: 1px solid #777 !important;
    }

    .dtr-table .col-date    { width: 20.0% !important; }
    .dtr-table .col-trans   { width: 16.0% !important; text-align: center !important; }
    .dtr-table .col-online  { width: 16.0% !important; text-align: right !important; }
    .dtr-table .col-offline { width: 16.0% !important; text-align: right !important; }
    .dtr-table .col-refund  { width: 16.0% !important; text-align: right !important; }
    .dtr-table .col-amount  { width: 16.0% !important; text-align: right !important; }

    .dtr-table td.t-center  { text-align: center !important; }
    .dtr-table td.t-left    { text-align: left !important; }
    .dtr-table td.t-right   { text-align: right !important; }
    .dtr-table td.fw-bold   { font-weight: 700 !important; }
    .dtr-table .nowrap      { white-space: nowrap !important; }

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
    .summary-val-center {
        text-align: center !important;
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

<div class="dtr-print-container">
    <div class="dtr-header-title"><?php echo html_escape($hospital_name); ?></div>
    <div class="dtr-header-subtitle"><?php echo html_escape($report_subtitle); ?></div>

    <?php if (!empty($print_rows)) { ?>
        <table class="dtr-table">
            <thead>
                <tr>
                    <th class="col-date">Date</th>
                    <th class="col-trans">Total Transaction</th>
                    <th class="col-online">Online (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-offline">Offline (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-refund">Refund Amount (<?php echo $currency_symbol; ?>)</th>
                    <th class="col-amount">Amount (<?php echo $currency_symbol; ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($print_rows as $row) { ?>
                    <tr>
                        <td class="col-date t-left nowrap"><?php echo html_escape($row['date']); ?></td>
                        <td class="col-trans t-center nowrap"><?php echo html_escape($row['total_transaction']); ?></td>
                        <td class="col-online t-right nowrap"><?php echo html_escape($row['online_transaction']); ?></td>
                        <td class="col-offline t-right nowrap"><?php echo html_escape($row['offline_transaction']); ?></td>
                        <td class="col-refund t-right nowrap <?php echo ($row['refund_val'] > 0) ? 'negative-amt' : ''; ?>">
                            <?php echo html_escape($row['refund_amount']); ?>
                        </td>
                        <td class="col-amount t-right nowrap <?php echo ($row['amount_val'] < 0) ? 'negative-amt' : ''; ?>">
                            <?php echo html_escape($row['amount']); ?>
                        </td>
                    </tr>
                <?php } ?>

                <!-- Totals row rendered strictly once at the end of the report (last page only) -->
                <tr class="summary-row">
                    <td class="summary-title">Total Amount</td>
                    <td class="summary-val-center"><?php echo number_format($total_transactions); ?></td>
                    <td class="summary-val"><?php echo number_format($total_online, 2); ?></td>
                    <td class="summary-val"><?php echo number_format($total_offline, 2); ?></td>
                    <td class="summary-val <?php echo ($total_refund > 0) ? 'negative-amt' : ''; ?>">
                        <?php echo ($total_refund > 0 ? '-' : '') . number_format($total_refund, 2); ?>
                    </td>
                    <td class="summary-val <?php echo ($net_total_amount < 0) ? 'negative-amt' : ''; ?>">
                        <?php echo number_format($net_total_amount, 2); ?>
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
