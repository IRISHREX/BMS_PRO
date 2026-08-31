<?php
$currency_symbol = isset($currency_symbol) ? $currency_symbol : '₹';
?>
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif !important;
        }
    }
    .referral-print-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        font-size: 11px;
        color: #111;
        width: 100%;
        background: #fff;
        padding: 5px 0;
    }
    .referral-doctor-block {
        width: 100%;
        margin-bottom: 18px;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .ref-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1px solid #777 !important;
        font-size: 11px !important;
        margin: 0 !important;
        background: #fff !important;
    }
    .ref-table th,
    .ref-table td {
        border: 1px solid #777 !important;
        padding: 4px 6px !important;
        line-height: 1.25 !important;
        vertical-align: middle !important;
        font-size: 11px !important;
        color: #000 !important;
    }
    .ref-table th {
        font-weight: 700 !important;
        text-align: center !important;
        background-color: #fff !important;
        color: #000 !important;
    }
    .ref-table .th-doctor {
        width: 22% !important;
        text-align: center !important;
    }
    .ref-table .th-billno {
        width: 12% !important;
        text-align: center !important;
    }
    .ref-table .th-billdate {
        width: 12% !important;
        text-align: center !important;
    }
    .ref-table .th-patient {
        width: 22% !important;
        text-align: center !important;
    }
    .ref-table .th-dept {
        width: 16% !important;
        text-align: center !important;
    }
    .ref-table .th-amount {
        width: 8% !important;
        text-align: center !important;
    }
    .ref-table .th-comm {
        width: 8% !important;
        text-align: center !important;
    }

    .ref-table td.doc-info-cell {
        text-align: center !important;
        vertical-align: middle !important;
        padding: 8px 6px !important;
        background-color: #fff !important;
    }
    .doc-name {
        font-weight: 700 !important;
        font-size: 11.5px !important;
        color: #000 !important;
        text-transform: uppercase !important;
        margin-bottom: 2px !important;
    }
    .doc-meta {
        font-size: 10px !important;
        color: #222 !important;
        text-transform: uppercase !important;
        line-height: 1.35 !important;
    }

    .ref-table td.month-header-cell {
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 5px 8px !important;
        background-color: #fff !important;
        color: #000 !important;
    }

    .ref-table td.t-center { text-align: center !important; }
    .ref-table td.t-left { text-align: left !important; }
    .ref-table td.t-right { text-align: right !important; }
    .ref-table td.fw-bold { font-weight: 700 !important; }
    .nowrap { white-space: nowrap !important; }

    .ref-table td.total-title-cell {
        text-align: center !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
    }

    .ref-thankyou {
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #222 !important;
        margin-top: 5px !important;
        margin-bottom: 14px !important;
    }
    .no-data-msg {
        text-align: center;
        padding: 40px;
        font-size: 13px;
        color: #666;
    }
</style>

<div class="referral-print-container">
    <?php if (!empty($grouped_doctors)) { ?>
        <?php foreach ($grouped_doctors as $doc_id => $doc) { 
            $bill_count = count($doc['bills']);
            // Rowspan = 1 (Month banner) + number of bills + 1 (TOTAL row)
            $doc_rowspan = $bill_count + 2;
        ?>
            <div class="referral-doctor-block">
                <table class="ref-table">
                    <thead>
                        <tr>
                            <th class="th-doctor">Doctor Name</th>
                            <th class="th-billno">Bill No</th>
                            <th class="th-billdate">Bill Date</th>
                            <th class="th-patient">Patient Name</th>
                            <th class="th-dept">Department</th>
                            <th class="th-amount">Amount</th>
                            <th class="th-comm">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: Doctor info on left (spans down) + Month/Hospital banner across remaining 6 columns -->
                        <tr>
                            <td rowspan="<?php echo $doc_rowspan; ?>" class="doc-info-cell">
                                <div class="doc-name"><?php echo html_escape($doc['doctor_name']); ?></div>
                                <?php if (!empty($doc['doctor_address'])) { ?>
                                    <div class="doc-meta"><?php echo html_escape($doc['doctor_address']); ?></div>
                                <?php } ?>
                                <?php if (!empty($doc['doctor_contact'])) { ?>
                                    <div class="doc-meta"><?php echo html_escape($doc['doctor_contact']); ?></div>
                                <?php } ?>
                            </td>
                            <td colspan="6" class="month-header-cell">
                                <?php echo html_escape($period_label . ' (' . $hospital_name . ')'); ?>
                            </td>
                        </tr>

                        <!-- Bill rows -->
                        <?php foreach ($doc['bills'] as $bill) { ?>
                            <tr>
                                <td class="t-center nowrap"><?php echo html_escape($bill['bill_no']); ?></td>
                                <td class="t-center nowrap"><?php echo html_escape($bill['bill_date']); ?></td>
                                <td class="t-left"><?php echo html_escape(strtoupper($bill['patient_name'])); ?></td>
                                <td class="t-left"><?php echo html_escape($bill['department']); ?></td>
                                <td class="t-right nowrap"><?php echo $currency_symbol . ' ' . number_format($bill['bill_amount'], 2); ?></td>
                                <td class="t-right nowrap"><?php echo $currency_symbol . ' ' . number_format($bill['commission'], 2); ?></td>
                            </tr>
                        <?php } ?>

                        <!-- Total row -->
                        <tr>
                            <td colspan="4" class="total-title-cell fw-bold">TOTAL</td>
                            <td class="t-right fw-bold nowrap"><?php echo $currency_symbol . ' ' . number_format($doc['total_amount'], 2); ?></td>
                            <td class="t-right fw-bold nowrap"><?php echo $currency_symbol . ' ' . number_format($doc['total_commission'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="ref-thankyou">
                    Thank You for Staying Connected with Us.
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="no-data-msg">
            <?php echo $this->lang->line('no_record_found') ?: 'No records found'; ?>
        </div>
    <?php } ?>
</div>
