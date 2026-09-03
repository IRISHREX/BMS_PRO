<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();

// Status calculation
$net_amt  = isset($result["net_amount"]) ? (float)$result["net_amount"] : 0;
$paid_amt = isset($result["total_deposit"]) ? (float)$result["total_deposit"] : 0;
$due_amt  = max(0, $net_amt - $paid_amt);

if ($due_amt <= 0 && $net_amt > 0) {
    $payment_status = $this->lang->line('paid') ?: 'PAID';
    $status_color   = '#16a34a';
    $status_bg      = '#f0fdf4';
    $status_border  = '#22c55e';
} elseif ($paid_amt > 0 && $due_amt > 0) {
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

// Demographics formatting
$gender_str = !empty($result['gender']) ? $result['gender'] : '-';
$age_str = '';
if (!empty($result['patient_id'])) {
    $raw_age = $this->customlib->get_patient_current_age($result['patient_id']);
    if (preg_match('/(\d+)\s*[^,\d]+,\s*(\d+)\s*[^,\d]+,\s*(\d+)/i', $raw_age, $m)) {
        $age_str = $m[1] . "y," . $m[2] . "m," . $m[3] . "d";
    } elseif (preg_match('/(\d+)/', $raw_age, $m)) {
        $age_str = $m[1] . "y";
    } else {
        $age_str = '-';
    }
} elseif (!empty($result['age'])) {
    $age_str = $result['age'] . 'y';
} else {
    $age_str = '-';
}
$patient_demographics = "Gender: " . $gender_str . " / Age: " . $age_str;
?>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $this->lang->line('bill'); ?></title>
        <style>
            @media print {
                @page { size: auto; margin: 7mm 9mm; }
                .sh-print-info-block { padding: 8px 14px 6px !important; margin-bottom: 8px !important; }
            }
            .sh-receipt-heading-table { width: 100%; border-top: 2px solid #111; border-bottom: 1px solid #111; margin-bottom: 10px; border-collapse: collapse; }
            .sh-payment-status-badge { display: inline-block; font-size: 14px; font-weight: 800; letter-spacing: 1px; padding: 3px 12px; border-radius: 4px; line-height: 1.2; text-transform: uppercase; }
            .sh-print-info-2col { width: 100%; border-collapse: collapse; table-layout: fixed; }
            .sh-print-info-2col > tbody > tr > td { width: 50%; vertical-align: top; }
            .sh-print-info-table { width: 100%; border-collapse: collapse; }
            .sh-print-info-table th { font-size: 9.5px; font-weight: 600; color: #475569; padding: 2.5px 0; text-align: left !important; vertical-align: top; white-space: nowrap; }
            .sh-print-info-table td { font-size: 10.5px; font-weight: 700; color: #0f172a; padding: 2.5px 0 2.5px 6px; text-align: left !important; vertical-align: top; word-break: break-word; }
        </style>
</head>
        <div class="fixed-print-header"> 
            <?php if (!empty($print_details[0]['print_header'])) { ?>
                        <div>
                            <img src="<?php
                            if (!empty($print_details[0]['print_header'])) {
                                echo $this->media_storage->getImageURL($print_details[0]['print_header']);
                            }
                            ?>" class="img-fluid sh-avatar-cover" >
                        </div>
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
      <div class="content-body">          
    <div id="html-2-pdfwrapper">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <div class="">
                   
                    <!-- Top Status & Heading Bar -->
                    <table class="sh-receipt-heading-table">
                        <tr>
                            <td width="28%" class="text-start" style="font-size:11px; font-weight:600; color:#1e293b; white-space:nowrap;">
                                <span style="color:#64748b; font-weight:500;"><?php echo $this->lang->line('date'); ?>:</span>
                                <strong><?php echo date($this->customlib->getHospitalDateFormat(true, false), strtotime($result["date"])); ?></strong>
                            </td>
                            <td width="44%" class="text-center" style="font-size:13px; font-weight:700; letter-spacing:2px; text-transform:uppercase; padding:6px 0;">
                                <?php echo $this->lang->line('radiology_single_billing') ?: 'RADIOLOGY BILLING'; ?>
                            </td>
                            <td width="28%" class="text-end" style="padding-right:2px;">
                                <span class="sh-payment-status-badge" style="color:<?php echo $status_color; ?>; background:<?php echo $status_bg; ?>; border:1.5px solid <?php echo $status_border; ?>;">
                                    <?php echo $payment_status; ?>
                                </span>
                            </td>
                        </tr>
                    </table>

                    <!-- 2-Column Patient Details Box -->
                    <div class="sh-print-info-block" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px 14px 6px; margin-bottom: 10px; background: #f8fafc;">
                        <table class="sh-print-info-2col">
                            <tbody>
                                <tr>
                                    <!-- Column 1: Bill & Patient Identification -->
                                    <td style="width:50%; vertical-align:top; padding-right:12px;">
                                        <table class="sh-print-info-table">
                                            <colgroup><col style="width:38%"><col style="width:62%"></colgroup>
                                            <tr>
                                                <th style="text-align:left;"><?php echo $this->lang->line('bill')." ".$this->lang->line('no'); ?> :</th>
                                                <td style="text-align:left;"><?php echo $this->customlib->getSessionPrefixByType('radiology_billing').$result["id"]; ?></td>
                                            </tr>
                                            <tr>
                                                 <th style="text-align:left;"><?php echo $this->lang->line('patient_name'); ?> :</th>
                                                 <td style="text-align:left;"><?php echo ($result["patient_name"] ?: '-'); ?></td>
                                             </tr>
                                             <tr>
                                                 <td colspan="2" style="text-align:left; font-size:10.5px; color:#0f172a; padding:2.5px 0; white-space:nowrap;">
                                                     <span style="font-weight:normal; color:#475569;">Gender:</span> <strong style="font-weight:700; color:#0f172a;"><?php echo $gender_str; ?></strong>
                                                     <span style="color:#94a3b8; margin:0 4px;">/</span>
                                                     <span style="font-weight:normal; color:#475569;">Age:</span> <strong style="font-weight:700; color:#0f172a;"><?php echo $age_str; ?></strong>
                                                 </td>
                                             </tr>
                                        </table>
                                    </td>

                                    <!-- Column 2: Reference & Contact -->
                                    <td style="width:50%; vertical-align:top; padding-left:12px;">
                                        <table class="sh-print-info-table">
                                            <colgroup><col style="width:40%"><col style="width:60%"></colgroup>
                                            <tr>
                                                 <th style="text-align:left;"><?php echo $this->lang->line('consultant_doctor'); ?> :</th>
                                                 <td style="text-align:left;"><?php echo (preg_replace('/\s*\([^)]*\)$/', '', $result["doctor_name"]) ?: '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;"><?php echo $this->lang->line('referred_by'); ?> :</th>
                                                <td style="text-align:left;"><?php echo ($result["referral_person_name"] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;"><?php echo $this->lang->line('phone'); ?> :</th>
                                                <td style="text-align:left;"><?php echo ($result["mobileno"] ?: '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="text-align:left;"><?php echo $this->lang->line('address'); ?> :</th>
                                                <td style="text-align:left;"><?php echo ($result["address"] ?: '-'); ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="divider mt-10 mb-10"></div>
                    <table class="printablea4" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <th width="5%"><?php echo $this->lang->line('slip'); ?> #</th>
                            <th width="55%"><?php echo $this->lang->line('test') . " " . $this->lang->line('name'); ?></th> 
                            <th width="20%"><?php echo $this->lang->line('report_date') ; ?></th>
                            <th width="20%" class="text-end"><?php echo $this->lang->line('amount'); ?></th>
                        </tr>                       
                            <?php
                            $p_count = 1;
                            foreach ($detail as $bill) {
                                ?>
                                <tr>
                                    <td style="font-size:9.5px;"><?php echo $p_count++; ?></td>
                                    <td style="font-size:9.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <strong><?php echo $bill["test_name"]; ?></strong>
                                        <?php if (!empty($bill["short_name"])) { ?>
                                            <span style="font-size:8.5px; font-weight:normal; color:#64748b; margin-left:4px;">(<?php echo html_escape($bill["short_name"]); ?>)</span>
                                        <?php } ?>
                                    </td>
                                    <td style="font-size:9.5px; white-space:nowrap;"><?php echo date($this->customlib->getHospitalDateFormat(true,false), strtotime($bill["reporting_date"])); ?></td>
                                    <td class="text-end" style="font-size:9.5px; white-space:nowrap;"><?php echo $bill["apply_charge"]; ?></td>
                                </tr>
                            <?php
                            }
                            ?>                       
                    </table>
                    <div class="divider mt-10 mb-10"></div>
					<table class="printablea4 sh-print-right-50" >                
                        <?php if (!empty($result["total"])) {?>
                            <tr>
                                <th style="width: 30%;"><?php echo $this->lang->line('total') . " (" . $currency_symbol . ")"; ?></th>
                                <td class="text-end"><?php echo $result["total"]; ?></td>
                            </tr>
                        <?php }?>
                        <?php if (!empty($result["discount"])) {  ?>
                            <tr>
                                <th><?php echo $this->lang->line('discount') . " (" . $currency_symbol . ")";  ?></th>
                                <td class="text-end"><?php echo $result["discount"]; ?></td>
                            </tr>
                        <?php }?>
                        <?php if (!empty($result["tax"])) {  ?>
                            <tr>
                                <th><?php echo $this->lang->line('tax') . " (" . $currency_symbol . ")";  ?></th>
                                <td class="text-end"><?php echo $result["tax"]; ?></td>
                            </tr>
                        <?php }?>
                        <?php
                        if ((!empty($result["discount"])) && (!empty($result["tax"]))) {
                            if (!empty($result["net_amount"])) {
                                ?>
                        <tr>
                            <th><?php echo $this->lang->line('net_amount') . " (" . $currency_symbol . ")"; ?></th>
                            <td class="text-end"><?php echo $result["net_amount"]; ?></td>
                        </tr>
                                <?php
                        }
                        }
                        ?>
                        <?php if (!empty($result["total_deposit"])) {
                                ?>
                        <tr>
                            <th><?php echo $this->lang->line("paid_amount"); ?></th>
                            <td class="text-end"><?php echo $result["total_deposit"]; ?></td>
                        </tr>
                        <?php
                        } ?>
                        <?php if (!empty($result["refund_amount"])) {
                                ?>
                        <tr>
                            <th><?php echo $this->lang->line("refund_amount"); ?></th>
                            <td class="text-end"><?php echo $result["refund_amount"]; ?></td>
                        </tr>
                        <?php
                        } ?>
                        <tr>
                            <th><?php echo $this->lang->line("due_amount") ?: 'Due Amount'; ?></th>
                            <td class="text-end"><?php echo $result["net_amount"] - $result["total_deposit"] ; ?></td>
                        </tr>
                        <?php if (!empty($result["note"])) {?>                         
                        <?php }

if (!$print) {
    ?>
                            <tr id="generated_by">
                                <th><?php echo $this->lang->line('collected_by'); ?></th>
                                <td class="text-end"><?php echo $result["generated_byname"]; ?></td>
                            </tr>
                        <?php
}
?>
                    </table>                   
                    <div class="divider mt-10 mb-10"></div> 
                    <table class="printablea4 sh-print-right-30" width="100%">                       
                        <?php if (($print) != 'yes') { ?>
                            <tr id="generated_by">
                                <th><?php echo $this->lang->line('collected_by'); ?></th>
                                <td class="text-end"><?php echo $result["generated_byname"]; ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                    <div class="divider mt-10 mb-10"></div>
                    <div class="footer-fixed printfooter"> 
                        <p><?php
                            if (!empty($print_details[0]['print_footer'])) {
                                echo $print_details[0]['print_footer'];
                            }
                            ?></p>
                    </div>        
                </div>
            </div>
            <!--/.col (left) -->
        </div>
    </div>
     </div>
    </td></tr></tbody>
    <tfoot><tr><td>

    <?php
                    if (!empty($print_details[0]['print_footer'])) {
                        ?>
       <div class="footer-space">&nbsp;</div>
  <?php
}
?>



    </td></tr></tfoot>
  </table>
  <?php
                    if (!empty($print_details[0]['print_footer'])) {
                        ?>
  <div class="footer-fixed">
  
  <?php   echo $print_details[0]['print_footer'];?>
                
  </div>
  <?php
}
?>
</html>