<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();
$logged_in_user = $this->customlib->getAdminSessionUserName();
$printed_date = date('d-m-y h:i:sA');

// Extract details from $result
$ipd_no_str = $this->customlib->getSessionPrefixByType('ipd_no') . $result['ipdid'];
$case_ref_no = !empty($result['case_reference_id']) ? $result['case_reference_id'] : $ipd_no_str;
$admission_date = !empty($result['date']) ? date('d-M-Y h:i:s A', strtotime($result['date'])) : '';
$admission_date_no_sec = !empty($result['date']) ? date('d-M-Y h:i A', strtotime($result['date'])) : '';
$admission_date_short = !empty($result['date']) ? date('d-M-Y H:i:s', strtotime($result['date'])) : '';
$reg_no = !empty($result['patient_unique_id']) ? $result['patient_unique_id'] : $result['patient_id'];

$patient_name = composePatientName($result['patient_name'], $result['patient_id']);
$clean_patient_name = preg_replace('/\s*\([^)]*\)$/', '', $result['patient_name']);

$age = $this->customlib->get_patient_current_age($result['patient_id']);
$gender = !empty($result['gender']) ? ucfirst($result['gender']) : '';
$address = !empty($result['address']) ? $result['address'] : '';
$religion = !empty($result['religion']) ? $result['religion'] : '';
$marital_status = !empty($result['marital_status']) ? ucfirst($result['marital_status']) : '';
$phone = !empty($result['mobileno']) ? $result['mobileno'] : '';
$occupation = !empty($result['occupation']) ? $result['occupation'] : '';
$nationality = !empty($result['nationality']) ? $result['nationality'] : 'INDIAN';
$doctor_name = !empty($result['name']) ? 'DR. ' . strtoupper(trim($result['name'] . ' ' . $result['surname'])) : '';

$guardian_name = !empty($result['guardian_name']) ? $result['guardian_name'] : '';
$id_passport_no = !empty($result['identification_number']) ? $result['identification_number'] : '';
$relative_name = !empty($result['guardian_name']) ? $result['guardian_name'] : '';
$relation = !empty($result['guardian_relation']) ? strtoupper($result['guardian_relation']) : '';

$bed_info = '';
if (!empty($result['bed_name'])) {
    $bed_info = $result['bed_name'];
    if (!empty($result['bedgroup_name'])) {
        $bed_info .= '/' . $result['bedgroup_name'];
    }
}
$case_type = !empty($result['case_type']) ? $result['case_type'] : (!empty($result['symptoms']) ? $result['symptoms'] : '');
$under_care = !empty($result['refference']) ? $result['refference'] : 'N/A';
$tpa = !empty($result['organisation_name']) ? $result['organisation_name'] : 'N/A';
$insurance_id = !empty($result['insurance_id']) ? $result['insurance_id'] : '';

// Hospital info
$hosp_name = !empty($hospital_setting[0]['name']) ? $hospital_setting[0]['name'] : 'CHANDPUR NURSING HOME';
$hosp_address = !empty($hospital_setting[0]['address']) ? $hospital_setting[0]['address'] : 'CHANDPUR BORDER, CHANDPUR, PAKUR, JHARKHAND, PIN-816107';
$hosp_phone = !empty($hospital_setting[0]['phone']) ? $hospital_setting[0]['phone'] : '+91 - 7477715210';
$hosp_email = !empty($hospital_setting[0]['email']) ? $hospital_setting[0]['email'] : 'cnh71570@gmail.com';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Form - <?php echo html_escape($clean_patient_name); ?></title>
    <style type="text/css">
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            background: #fff;
        }
        .page-container {
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .page-break {
            page-break-before: always;
        }
        
        /* Hospital Header */
        .hosp-header {
            text-align: center;
            position: relative;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .hosp-header .logo-left {
            position: absolute;
            left: 0;
            top: 0;
            max-height: 65px;
        }
        .hosp-header .logo-right {
            position: absolute;
            right: 0;
            top: 0;
            max-height: 65px;
        }
        .hosp-header h1 {
            font-size: 24px;
            font-weight: 900;
            color: #1a365d;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .hosp-header p {
            margin: 2px 0;
            font-size: 10.5px;
            font-weight: 700;
            color: #2b6cb0;
        }
        
        /* Bar Header */
        .bar-title-box {
            border: 1.5px solid #000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            margin-top: 6px;
            background: #f7fafc;
        }
        
        /* Data Tables */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .grid-table td, .grid-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10.5px;
            vertical-align: top;
        }
        .grid-table td.lbl {
            font-weight: bold;
            width: 17%;
            background: #ffffff;
        }
        .grid-table td.val {
            width: 33%;
        }

        /* Consent & Norms */
        .consent-box {
            border: 1px solid #000;
            padding: 6px 8px;
            margin-bottom: 6px;
            font-size: 10px;
            text-align: justify;
        }
        .consent-box p {
            margin: 0 0 4px 0;
            line-height: 1.35;
        }
        
        .norms-box {
            border: 1px solid #000;
            padding: 6px 8px;
            margin-bottom: 8px;
            font-size: 9.5px;
        }
        .norms-title {
            font-weight: bold;
            font-size: 10.5px;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .norms-list {
            margin: 0;
            padding-left: 15px;
        }
        .norms-list li {
            margin-bottom: 2px;
        }

        /* Signatures */
        .sig-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 10.5px;
            font-weight: bold;
        }
        .sig-line-bottom {
            border-bottom: 1px dashed #000;
            margin-bottom: 8px;
            padding-bottom: 8px;
            font-size: 9.5px;
            font-weight: bold;
        }

        /* Office Use Box */
        .office-use-box {
            border: 1.5px solid #000;
            padding: 6px 8px;
            margin-top: 6px;
            margin-bottom: 6px;
        }
        .office-use-header {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
        }
        .office-use-header span {
            border: 1px solid #000;
            padding: 2px 12px;
            background: #fff;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
        }

        /* Footer */
        .print-footer {
            font-size: 9px;
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        /* Page 2 Specifics */
        .dot-item {
            margin-bottom: 5px;
            font-size: 10px;
        }
        .dot-leader {
            border-bottom: 1px dotted #000;
            width: 100%;
            display: inline-block;
        }

        .doctor-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .doctor-table th, .doctor-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
            text-align: left;
        }
        .doctor-table td {
            height: 20px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .sig-table td {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            width: 33.33%;
            vertical-align: bottom;
            height: 80px;
        }
    </style>
</head>
<body>
<div class="page-container">

    <!-- ════════════════ PAGE 1 ════════════════ -->
    <?php if (!empty($print_details[0]['print_header'])) { ?>
        <div style="width: 100%; overflow: hidden; margin-bottom: 8px; max-height: 72px;">
            <img src="<?php echo $this->media_storage->getImageURL($print_details[0]['print_header']); ?>" style="width: 100%; height: auto; display: block; margin-bottom: -22px;">
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

    <!-- Title Bar -->
    <div class="bar-title-box">
        <div>ADMISSION FORM: IPD / <?php echo date('Y', strtotime($result['date'])); ?> / <?php echo html_escape($result['ipdid']); ?></div>
        <div>ADMISSION DATE & TIME : <?php echo html_escape($admission_date); ?></div>
        <div>Reg. No: <?php echo html_escape($reg_no); ?></div>
    </div>

    <!-- Patient Main Info -->
    <table class="grid-table">
        <tr>
            <td class="lbl">Reg. No :</td>
            <td class="val"><?php echo html_escape($reg_no); ?></td>
            <td class="lbl">Patient Name :</td>
            <td class="val"><strong><?php echo html_escape(strtoupper($clean_patient_name)); ?></strong></td>
        </tr>
        <tr>
            <td class="lbl">Age :</td>
            <td class="val"><?php echo html_escape($age); ?></td>
            <td class="lbl">Gender :</td>
            <td class="val"><?php echo html_escape($gender); ?></td>
        </tr>
        <tr>
            <td class="lbl">Address :</td>
            <td class="val"><?php echo html_escape($address); ?></td>
            <td class="lbl">Religion :</td>
            <td class="val"><?php echo html_escape(strtoupper($religion)); ?></td>
        </tr>
        <tr>
            <td class="lbl">Marital Status :</td>
            <td class="val"><?php echo html_escape($marital_status); ?></td>
            <td class="lbl">Phone No :</td>
            <td class="val"><?php echo html_escape($phone); ?></td>
        </tr>
        <tr>
            <td class="lbl">Occupation :</td>
            <td class="val"><?php echo html_escape(strtoupper($occupation)); ?></td>
            <td class="lbl">Nationality :</td>
            <td class="val"><?php echo html_escape(strtoupper($nationality)); ?></td>
        </tr>
        <tr>
            <td class="lbl">Doctor :</td>
            <td class="val" colspan="3"><strong><?php echo html_escape($doctor_name); ?></strong></td>
        </tr>
    </table>

    <!-- Guardian & Relative Info -->
    <table class="grid-table">
        <tr>
            <td class="lbl">W/O S/O D/O :</td>
            <td class="val"><?php echo html_escape(strtoupper($guardian_name)); ?></td>
            <td class="lbl">ID/PASSPORT NO :</td>
            <td class="val"><?php echo html_escape($id_passport_no); ?></td>
        </tr>
        <tr>
            <td class="lbl">RELATIVE NAME :</td>
            <td class="val"><?php echo html_escape(strtoupper($relative_name)); ?></td>
            <td class="lbl">RELATION :</td>
            <td class="val"><?php echo html_escape($relation); ?></td>
        </tr>
    </table>

    <!-- Bed & Case Info -->
    <table class="grid-table">
        <tr>
            <td class="lbl">BED NO. :</td>
            <td class="val"><?php echo html_escape($bed_info); ?></td>
            <td class="lbl">CASE TYPE :</td>
            <td class="val"><?php echo html_escape(strtoupper($case_type)); ?></td>
        </tr>
        <tr>
            <td class="lbl">UNDER CARE :</td>
            <td class="val" colspan="3"><?php echo html_escape($under_care); ?></td>
        </tr>
        <tr>
            <td class="lbl">TPA :</td>
            <td class="val"><?php echo html_escape($tpa); ?></td>
            <td class="lbl">INSURANCE ID :</td>
            <td class="val"><?php echo html_escape($insurance_id); ?></td>
        </tr>
    </table>

    <!-- Consent Box -->
    <div class="consent-box">
        <p>I do hereby give my full consent to undertake treatment of above patient by Medical Management, Surgical Management, Instensive Care at this Nursing Home.</p>
        <p>I agree to pay all the bills and when submitted by hospital authority for my/my patient's treatment, and clear all the dues of Nursing Hme incurred for the treatment of the patient before discharge/DORB.</p>
        <p>I shall not hold the institution, it's staff and the doctors responsible for any unwanted consequences during the course of medical treatment and the surgery administration of anaesthesia / drug or investigation/ treatment etc.</p>
        <p>I have been fully explained the consequences of the procedures and their risks.</p>
    </div>

    <!-- General Norms Box -->
    <div class="norms-box">
        <div class="norms-title">GENERAL NORMS FOR PATIENT ADMISSION</div>
        <ol class="norms-list">
            <li>An <strong>ADVANCE PAYMENT</strong> should be made ar the time of admission accordingly.<br>
                a) Rs. 10000/- For <strong>GENERAL WARD</strong><br>
                b) Rs. 15000/- For <strong>CABINS</strong><br>
                c) Rs. 30000/- For <strong>ICU/HDU</strong>
            </li>
            <li>A minimum of 80% to 85% amount of the surgery package must be paid before the operation.</li>
            <li>Patient should NOT bear any cash,valuables,mobile phone etc. during his/her stay in the Nursing Home.</li>
            <li>Only two persons are allowed during visiting hours,childrens are allowed only on Sunday evening.</li>
            <li>No foods from outside are allowed without prior permission.</li>
            <li>Patient availing cashless facility should submit His/Her documents at the Insurance Desk.</li>
            <li>Patient party should enquire about there outstanding payment regularly from the respective counters,so that maximum outstanding does not exceeds Rs.10,000/-.</li>
            <li>Shifting from ICU to Ward depends on bed availability.</li>
            <li>PATIENT / PARTIES ID DOCUMENT IS MANDATARY. PLEASE PROVIDE US AT THE EARLIEST.</li>
        </ol>
    </div>

    <!-- Signatures Page 1 -->
    <div class="sig-row">
        <div>
            Witness Signature with relation :<br>
            Contact No :
        </div>
        <div style="border-top: 1px solid black; margin-top:10px;">
            Signature of Patient/Party
        </div>
    </div>
    <div class="sig-line-bottom">
        Full charge on the day of admission. No charge if the patient leaves before 11:20 am on the day of discahrge
    </div>

    <!-- Office Use Box -->
    <div class="office-use-box">
        <div class="office-use-header">
            <span>FOR OFFICE USE</span>
        </div>
        <table style="width: 100%; font-size: 10px; border-collapse: collapse; margin-bottom: 6px;">
            <tr>
                <td style="width: 44%; white-space: nowrap;"><strong>Date & Time of Admission :</strong> <span style="white-space: nowrap;"><?php echo html_escape($admission_date_no_sec); ?></span></td>
                <td style="width: 28%;"><strong>Admission No:</strong> <?php echo html_escape($ipd_no_str); ?></td>
                <td style="width: 28%;"><strong>Bed No :</strong> <?php echo html_escape($bed_info); ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>Under Care Doctor :</strong> </td>
                <td><strong>Case Type :</strong> <?php echo html_escape(strtoupper($case_type)); ?></td>
            </tr>
        </table>
        <table style="width: 100%; font-size: 10px; border-collapse: collapse; margin-bottom: 8px;">
            <tr>
                <td style="width: 25%;"><strong>VEGETERIAN</strong></td>
                <td style="width: 25%;">&#9633; YES &nbsp;&nbsp;&nbsp;&nbsp; &#9633; NO</td>
                <td style="width: 50%;"><strong>Insurance /TPA / CORPORATE</strong></td>
            </tr>
            <tr>
                <td><strong>Patient's History</strong></td>
                <td>&#9633; YES &nbsp;&nbsp;&nbsp;&nbsp; &#9633; NO</td>
                <td>&#9633; Asthma &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9633; Cardiac</td>
            </tr>
            <tr>
                <td><strong>Allergies to Food and/or Drugs</strong></td>
                <td>&#9633; Aspirin/Ecosprin &nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td>&#9633; Clopitogril &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9633; Others</td>
            </tr>
        </table>
    </div>

    <!-- Front Office Executive Signature Outside Box -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 90px; margin-bottom: 8px; font-size: 10px; font-weight: bold;">
        <div>Signature of the Front Office Executive :</div>
        <div style="margin-right: 100px">DATE :</div>
    </div>

    <!-- Print Footer Page 1 -->
    <div class="print-footer">
        Printed by : <?php echo html_escape($logged_in_user . '@' . $printed_date); ?>
    </div>


    <!-- ════════════════ PAGE 2 ════════════════ -->
    <div class="page-break"></div>

    <?php if (!empty($print_details[0]['print_header'])) { ?>
        <div style="width: 100%; overflow: hidden; margin-bottom: 8px; margin-top: 8px; max-height: 72px;">
            <img src="<?php echo $this->media_storage->getImageURL($print_details[0]['print_header']); ?>" style="width: 100%; height: auto; display: block; margin-bottom: -22px;">
        </div>
    <?php } else { ?>
        <div class="hosp-header" style="margin-top: 10px;">
            <?php if (!empty($hospital_setting[0]['mini_logo'])): ?>
                <img class="logo-left" src="<?php echo base_url('uploads/hospital_content/logo/' . $hospital_setting[0]['mini_logo']); ?>" alt="Logo">
                <img class="logo-right" src="<?php echo base_url('uploads/hospital_content/logo/' . $hospital_setting[0]['mini_logo']); ?>" alt="Logo">
            <?php endif; ?>
            <h1><?php echo html_escape($hosp_name); ?></h1>
            <p><?php echo html_escape($hosp_address); ?></p>
            <p>Mobile : <?php echo html_escape($hosp_phone); ?> &nbsp;&nbsp;&nbsp;&nbsp; E-mail : <?php echo html_escape($hosp_email); ?></p>
        </div>
    <?php } ?>

    <!-- Title Bar Page 2 -->
    <div class="bar-title-box">
        <div>ADMISSION FORM: IPD / <?php echo date('Y', strtotime($result['date'])); ?> / <?php echo html_escape($result['ipdid']); ?></div>
        <div>ADMISSION DATE & TIME : <?php echo html_escape($admission_date); ?></div>
        <div>Reg. No: <?php echo html_escape($reg_no); ?></div>
    </div>

    <!-- Compact Patient Summary -->
    <table class="grid-table">
        <tr>
            <td class="lbl">PATIENT NAME :</td>
            <td class="val"><strong><?php echo html_escape(strtoupper($clean_patient_name)); ?></strong></td>
            <td class="lbl">AGE / GENDER :</td>
            <td class="val"><?php echo html_escape($age . ' / ' . $gender); ?></td>
        </tr>
        <tr>
            <td class="lbl">ADDRESS :</td>
            <td class="val"><?php echo html_escape($address); ?></td>
            <td class="lbl">RELIGION :</td>
            <td class="val"><?php echo html_escape(strtoupper($religion)); ?></td>
        </tr>
        <tr>
            <td class="lbl">OCCUPATION :</td>
            <td class="val"><?php echo html_escape(strtoupper($occupation)); ?></td>
            <td class="lbl">MARITAL STATUS :</td>
            <td class="val"><?php echo html_escape($marital_status); ?></td>
        </tr>
        <tr>
            <td class="lbl">W/O S/O D/O :</td>
            <td class="val"><?php echo html_escape(strtoupper($guardian_name)); ?></td>
            <td class="lbl">PHONE NO :</td>
            <td class="val"><?php echo html_escape($phone); ?></td>
        </tr>
        <tr>
            <td class="lbl">RELATIVE NAME :</td>
            <td class="val"><?php echo html_escape(strtoupper($relative_name)); ?></td>
            <td class="lbl">NATIONALITY :</td>
            <td class="val"><?php echo html_escape(strtoupper($nationality)); ?></td>
        </tr>
        <tr>
            <td class="lbl">RELATION :</td>
            <td class="val"><?php echo html_escape($relation); ?></td>
            <td class="lbl">ID/PASSPORT NO :</td>
            <td class="val"><?php echo html_escape($id_passport_no); ?></td>
        </tr>
    </table>

    <!-- Dotted Checklist Lines Page 2 -->
    <div style="font-size: 10px; line-height: 1.8; margin-top: 8px;">
        <div class="dot-item"># (a) Date of first attendance for treatment ................................................................................................................................................</div>
        <div class="dot-item">(b) Date and Time of Admission......................................................................................................................................................</div>
        <div class="dot-item">(c) Date and Time of Discharge.....................................................................................................................................................</div>
        <div class="dot-item">(d) Date and Time of Death..........................................................................................................................................................</div>
        <div class="dot-item" style="margin-top: 4px;"># In case of delivery of the patients ....................................................................................................................................................</div>
        <div class="dot-item">(a) Date and Time of delivery...........................................................................................................................................................</div>
        <div class="dot-item">(b) No. of new born infant...............................................................................................................................................................</div>
        <div class="dot-item">(c) Weight of the baby...................................................................................................................................................................</div>
        <div class="dot-item">(d) Sex of the baby ......................................................................................................................................................................</div>
        <div class="dot-item">(e) Live of still birth ........................................................................................................................................................................</div>
        <div class="dot-item">(f) Date and Time of miscarrigae if any ..............................................................................................................................................</div>
        <div class="dot-item">(g) Baby vaccinated (BCG/OPV)? ....................................................................................................................................................</div>
        <div class="dot-item">(h) Mother completely immunized? (Yes/No) ....................................................................................................................................</div>
        <div class="dot-item">(i) Method of Delivery: Normal/Forceps/Caesarian Section ...................................................................................................................</div>
        <div class="dot-item">(j) Name(s) of conducting Doctor(s) with qualification and Registration No ................................................................................................</div>
        <div class="dot-item">(k) Name of Nurses/Midwives assisting delivery with qualification and Registration No ................................................................................</div>
    </div>

    <!-- Attending Doctor Table -->
    <div style="font-weight: bold; font-size: 10px; margin-top: 10px;"># Particulars of Doctor(s) attended the patient</div>
    <table class="doctor-table">
        <thead>
            <tr>
                <th style="width: 28%;">Name in Full</th>
                <th style="width: 28%;">&gt; Address</th>
                <th style="width: 22%;">&gt; Qualification</th>
                <th style="width: 22%;">&gt;Registration</th>
            </tr>
        </thead>
        <tbody>
            <tr><td></td><td></td><td></td><td></td></tr>
            <tr><td></td><td></td><td></td><td></td></tr>
            <tr><td></td><td></td><td></td><td></td></tr>
        </tbody>
    </table>

    <!-- Clinical Diagnosis -->
    <div style="font-size: 10px; line-height: 1.6; margin-top: 6px;">
        <div><strong>7.Clinical Diagnosis</strong></div>
        <div style="border-bottom: 1px dotted #000; height: 18px; margin-bottom: 6px;"></div>
        <div style="font-size: 9.5px;">[A case records of each patient( and any child born to the patient) Where all details of illness and treatment shall be entered.]</div>
        <div style="font-size: 9.5px; margin-top: 6px;">8. In case of Death of a patient of birth of a child, Where necessary intimation has been sent to appropriate authority or not.</div>
    </div>

    <!-- Triple Signature Box Page 2 -->
    <table class="sig-table">
        <tr>
            <td>Signature of the licensee with date</td>
            <td>Signature of the RMO with date</td>
            <td>Signature of the attending doctor with date</td>
        </tr>
    </table>

    <!-- Print Footer Page 2 -->
    <div class="print-footer">
        Printed by : <?php echo html_escape($logged_in_user . '@' . $printed_date); ?>
    </div>

</div>
</body>
</html>
