<?php
$print_date = date($this->customlib->getHospitalDateFormat(true, false));
?>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $this->lang->line('prescription'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/sh-print.css">
    <?php include(APPPATH . 'views/admin/shared/_print_css.php'); ?>
</head>
<body>

<div class="fixed-print-header">
    <?php if (!empty($print_details['print_header'])) : ?>
        <img src="<?php echo $this->media_storage->getImageURL($print_details['print_header']); ?>" style="max-height:80px; width:100%; object-fit:contain;" class="img-fluid">
    <?php else : ?>
        <div style="padding: 8px 0;"><span style="font-size: 16px; font-weight: bold;"><?php echo $this->lang->line('prescription'); ?></span></div>
    <?php endif; ?>
</div>

<table class="table-print-full" width="100%">
    <thead>
        <tr><td><div class="header-space">&nbsp;</div></td></tr>
    </thead>
    <tbody>
        <tr><td>
        <div class="content-body sh-px-12" >
        <div class="print-area">

<?php
$raw_age = $this->customlib->get_patient_current_age($result['patientid']);
$compact_age = str_ireplace(
    [' Year, ', ' Years, ', ' Month, ', ' Months, ', ' Day', ' Days', ' Year', ' Month'],
    ['y,', 'y,', 'm,', 'm,', 'd', 'd', 'y', 'm'],
    $raw_age
);
if (empty($compact_age)) {
    $compact_age = '-';
}
?>

            <!-- ① Receipt Heading with Date on Top-Left and Centered Title -->
            <table class="sh-receipt-heading-table">
                <tr>
                    <td style="width:32%; text-align:left; vertical-align:middle; font-size:11px; font-weight:600; color:#1e293b; white-space:nowrap;">
                        <span style="color:#64748b; font-weight:500;"><?php echo $this->lang->line('date'); ?>:</span>
                        <strong><?php echo date($this->customlib->getHospitalDateFormat(true, true)); ?></strong>
                    </td>
                    <td style="width:46%; text-align:center; vertical-align:middle; padding:6px 0; font-size:13px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#111;">
                        <?php echo $this->lang->line('prescription'); ?>
                    </td>
                    <td style="width:22%; text-align:right; vertical-align:middle; padding-right:2px;">
                        &nbsp;
                    </td>
                </tr>
            </table>

            <!-- ② 2-Column Patient Details Box -->
            <div class="sh-print-info-block">
                <table class="sh-print-info-2col">
                    <tbody>
                        <tr>
                            <!-- Column 1: OPD & Patient Details -->
                            <td style="width:50%; vertical-align:top; padding-right:12px;">
                                <table class="sh-print-info-table">
                                    <colgroup><col style="width:38%"><col style="width:62%"></colgroup>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('opd_no'); ?></th>
                                        <td style="text-align:left;"><?php echo ($result['opd_details_id'] ? $opd_prefix . $result['opd_details_id'] : '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('patient_name'); ?></th>
                                        <td style="text-align:left;"><?php echo ($result['patient_name'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; font-size:10.5px; color:#0f172a; padding:2.5px 0; white-space:nowrap;">
                                            <span style="font-weight:normal; color:#475569;"><?php echo $this->lang->line('gender'); ?>:</span> <strong style="font-weight:700; color:#0f172a;"><?php echo ($result['gender'] ?: '-'); ?></strong>
                                            <span style="color:#94a3b8; margin:0 4px;">/</span>
                                            <span style="font-weight:normal; color:#475569;"><?php echo $this->lang->line('age'); ?>:</span> <strong style="font-weight:700; color:#0f172a;"><?php echo $compact_age; ?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('blood_group'); ?></th>
                                        <td style="text-align:left;"><?php echo ($blood_group_name ?: '-'); ?></td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Column 2: Clinical Reference & Contact -->
                            <td style="width:50%; vertical-align:top; padding-left:12px;">
                                <table class="sh-print-info-table">
                                    <colgroup><col style="width:42%"><col style="width:58%"></colgroup>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('checkup_id'); ?></th>
                                        <td style="text-align:left;"><?php echo ($visitid ? $this->customlib->getSessionPrefixByType('checkup_id') . $visitid : '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('consultant_doctor'); ?></th>
                                        <td style="text-align:left;"><?php echo ($result['name'] ? trim($result['name'] . ' ' . $result['surname']) : '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('address'); ?></th>
                                        <td style="text-align:left;"><?php echo ($result['address'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left;"><?php echo $this->lang->line('known_allergies'); ?></th>
                                        <td style="text-align:left;"><?php echo ($result['known_allergies'] ?: '-'); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Divider for clarity -->
            <div class="sh-section-divider"></div>

            <!-- Vitals -->
            <?php if (!empty($result['bp']) || !empty($result['height']) || !empty($result['weight']) || !empty($result['pulse']) || !empty($result['temperature']) || !empty($result['respiration'])) : ?>
            <div class="sh-print-section-title"><?php echo $this->lang->line('vitals'); ?></div>
            <table class="sh-print-table">
                <thead>
                    <tr>
                        <th class="sh-col-16 text-start"><?php echo $this->lang->line('bp'); ?></th>
                        <th class="sh-col-16 text-start"><?php echo $this->lang->line('height'); ?></th>
                        <th class="sh-col-16 text-start"><?php echo $this->lang->line('weight'); ?></th>
                        <th class="sh-col-16 text-start"><?php echo $this->lang->line('pulse'); ?></th>
                        <th class="sh-col-16 text-start"><?php echo $this->lang->line('temperature'); ?></th>
                        <th class="sh-col-20 text-start"><?php echo $this->lang->line('respiration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold"><?php echo ($result['bp'] ?: '-'); ?></td>
                        <td class="fw-bold"><?php echo ($result['height'] ?: '-'); ?></td>
                        <td class="fw-bold"><?php echo ($result['weight'] ?: '-'); ?></td>
                        <td class="fw-bold"><?php echo ($result['pulse'] ?: '-'); ?></td>
                        <td class="fw-bold"><?php echo ($result['temperature'] ?: '-'); ?></td>
                        <td class="fw-bold"><?php echo ($result['respiration'] ?: '-'); ?></td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- The rest of the page is left blank for the doctor to write manually -->

        </div>
        </div>
        </td></tr>
    </tbody>
    <tfoot>
        <tr><td>
            <?php if (!empty($print_details['print_footer'])) : ?>
            <div class="footer-space">&nbsp;</div>
            <?php endif; ?>
        </td></tr>
    </tfoot>
</table>

<?php if (!empty($print_details['print_footer'])) : ?>
<div class="footer-fixed">
    <?php echo $print_details['print_footer']; ?>
</div>
<?php endif; ?>

</body>
</html>
