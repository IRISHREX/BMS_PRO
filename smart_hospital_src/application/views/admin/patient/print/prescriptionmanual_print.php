<?php
$print_date_only = date($this->customlib->getHospitalDateFormat(true, false));

// Fetch dynamic hospital details
$hospital_setting = $this->setting_model->getHospitalDetail();

$h_name = !empty($hospital_setting->name) ? $hospital_setting->name : 'DHULIYAN MAX HEALTH CARE PVT. LTD.';
$h_address = !empty($hospital_setting->address) ? $hospital_setting->address : 'Vill.-New Housenagar, P.O.- Tinpakuria, P.S- Samserganj Dist.- Murshidabad, Pin-742202';
$h_email = !empty($hospital_setting->email) ? $hospital_setting->email : 'dhuliyanmaxhealthcare@gmail.com';
$h_phone = !empty($hospital_setting->phone) ? $hospital_setting->phone : '9641955177';

// Hospital logo resolution
$h_logo_file = !empty($hospital_setting->mini_logo) ? $hospital_setting->mini_logo : (!empty($hospital_setting->image) ? $hospital_setting->image : (!empty($hospital_setting->app_logo) ? $hospital_setting->app_logo : ''));
$h_logo_url = '';
if (!empty($h_logo_file)) {
    if (strpos($h_logo_file, 'uploads/') !== false) {
        $h_logo_url = $this->media_storage->getImageURL($h_logo_file);
    } else {
        $h_logo_url = $this->media_storage->getImageURL('uploads/hospital_content/logo/' . $h_logo_file);
    }
}

// Doctor Details
$doc_name = !empty($result['name']) ? trim($result['name'] . ' ' . $result['surname']) : '';
if (!empty($doc_name) && stripos($doc_name, 'Dr.') === false) {
    $doc_name = 'Dr. ' . $doc_name;
}
if (empty($doc_name)) {
    $doc_name = 'Dr. Pritam Mitra';
}

$doc_qual = !empty($result['qualification']) ? $result['qualification'] : (!empty($result['designation']) ? $result['designation'] : '');
if (empty($doc_qual)) {
    $doc_qual = "M.B.B.S, MS (SURGERY, PGD, MCH\n(NATIONAL INSTITUTE OF HEALTH\nSCIENCE) FELLOWSHIP MAS\n(UK) LIFE MEMBER ASI, AMASI";
}

$doc_spec_parts = array_filter([
    !empty($result['specialist_name']) ? $result['specialist_name'] : (!empty($result['specialist']) && !is_numeric($result['specialist']) ? $result['specialist'] : ''),
    !empty($result['specialization']) ? $result['specialization'] : '',
    !empty($result['note']) ? $result['note'] : ''
]);
$doc_spec = !empty($doc_spec_parts) ? implode(' | ', $doc_spec_parts) : 'Every Monday & Thursday 3 P.M';

// Patient Details
$raw_age = !empty($result['patientid']) ? $this->customlib->get_patient_current_age($result['patientid']) : '';
$compact_age = '';
if (!empty($raw_age)) {
    $compact_age = str_ireplace(
        [' Years, ', ' Year, ', ' Months, ', ' Month, ', ' Days', ' Day', ' Years', ' Year', ' Months', ' Month'],
        ['y,', 'y,', 'm,', 'm,', 'd', 'd', 'y', 'y', 'm', 'm'],
        $raw_age
    );
    $compact_age = preg_replace('/\s+/', '', $compact_age);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $this->lang->line('prescription'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/sh-print.css">
    <?php include(APPPATH . 'views/admin/shared/_print_css.php'); ?>
    <style>
    /* Full Page Layout & Print Dialog Options */
    @page {
        size: auto;
        margin: 3mm 4mm;
    }
    
    html, body {
        height: 100%;
        min-height: 100%;
        margin: 0;
        padding: 0;
        background: #fff;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    @media print {
        html, body {
            height: 100% !important;
            min-height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .fixed-print-header, .header-space, .footer-fixed, .footer-space {
            display: none !important;
        }
        .table-print-full, .content-body, .print-area {
            height: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            display: block !important;
        }
        .presc-pad-print {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            min-height: calc(100vh - 6mm) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }

    .presc-pad-print {
        font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
        background: #fff;
        border: 1px solid #cbd5e1;
        width: 100%;
        height: 100%;
        min-height: 98vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Dynamic Hospital Header Banner */
    .presc-header-banner {
        width: 100%;
        background: #fff;
        overflow: hidden;
        flex: 0 0 auto;
    }
    .presc-header-top {
        background: #152836;
        display: flex;
        align-items: center;
        position: relative;
        min-height: 54px;
        max-height: 64px;
        overflow: hidden;
    }
    .presc-header-left-shapes {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 140px;
        z-index: 1;
    }
    .presc-logo-wrap {
        position: relative;
        z-index: 2;
        margin-left: 8px;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }
    .presc-logo-circle-container {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #004467;
        box-shadow: 0 0 0 2px #00c0ef;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .presc-logo-img {
        width: 38px;
        height: 38px;
        object-fit: contain;
        border-radius: 50%;
    }
    .presc-header-title-wrap {
        flex: 1;
        text-align: center;
        padding-left: 50px;
        padding-right: 12px;
        z-index: 2;
        overflow: hidden;
    }
    .presc-hospital-title {
        color: #ffc107;
        font-size: clamp(13px, 2.2vw, 17px);
        font-weight: 900;
        letter-spacing: 0.5px;
        margin: 0;
        text-transform: uppercase;
        font-family: 'Arial Black', Impact, sans-serif;
        line-height: 1.15;
        word-break: break-word;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .presc-header-sub {
        background: #fff;
        border-bottom: 2.5px solid #00c0ef;
        padding: 4px 6px 3px 6px;
        text-align: center;
        color: #1e293b;
        line-height: 1.25;
        overflow: hidden;
    }
    .presc-header-address {
        font-size: 10px;
        font-weight: 600;
        margin-bottom: 1px;
        word-break: break-word;
        line-height: 1.2;
    }
    .presc-header-contact {
        font-size: 10px;
        font-weight: 600;
        line-height: 1.2;
    }

    /* Doctor & Patient Info Box */
    .presc-top-box-print {
        border-top: 2px solid #d32f2f;
        border-bottom: 2px solid #d32f2f;
        width: 100%;
        display: flex;
        background: #fff;
        flex: 0 0 auto;
    }
    .presc-doc-col {
        width: 44%;
        border-right: 2px solid #d32f2f;
        padding: 6px 8px;
        box-sizing: border-box;
    }
    .presc-patient-col {
        width: 56%;
        padding: 6px 10px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .presc-doc-title {
        color: #d32f2f;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 4px;
    }
    .presc-doc-qual-row {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .presc-doc-qual {
        color: #004467;
        font-size: 8.5px;
        font-weight: 700;
        line-height: 1.25;
        text-transform: uppercase;
    }
    .presc-doc-timing {
        color: #004467;
        font-size: 9.5px;
        font-weight: 700;
        text-align: center;
        margin-top: 4px;
    }

    /* Patient Form Lines with Dotted Underline */
    .presc-pat-line {
        display: flex;
        align-items: flex-end;
        margin-bottom: 4px;
    }
    .presc-label {
        color: #0088cc;
        font-weight: 600;
        font-style: italic;
        white-space: nowrap;
        font-size: 11.5px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .presc-dots-line {
        border-bottom: 1.5px dotted #0088cc;
        color: #0f172a;
        font-style: normal;
        font-weight: 600;
        padding-left: 4px;
        display: inline-block;
        font-size: 11.5px;
        min-height: 16px;
        flex: 1;
        margin-left: 3px;
        line-height: 1.2;
    }
    .presc-pat-inline-row {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }
    .presc-pat-inline-cell {
        display: flex;
        align-items: flex-end;
    }

    /* Main Prescription Body - Dynamically Expands to Full Remaining Page Height */
    .presc-print-body {
        width: 100%;
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        background: #fff;
    }
    .presc-print-left {
        width: 25%;
        border-right: 2px solid #d32f2f;
        padding: 12px 8px;
        background: #fff;
        box-sizing: border-box;
    }
    .presc-print-right {
        width: 75%;
        padding: 10px 14px;
        background: #fff;
        position: relative;
        flex: 1;
        box-sizing: border-box;
    }
    .presc-obs-item {
        margin-bottom: 24px;
        font-size: 11.5px;
    }
    .presc-obs-label {
        color: #0088cc;
        font-weight: 600;
    }
    .presc-obs-val {
        color: #1e293b;
        font-weight: 600;
        margin-left: 4px;
    }
    .presc-rx-badge {
        font-size: 28px;
        font-weight: bold;
        color: #d32f2f;
        font-family: 'Times New Roman', Times, serif;
        line-height: 1;
        margin-bottom: 6px;
    }

    /* Bottom Emergency Notice Footer - Firmly Placed at Bottom */
    .presc-print-footer {
        border-top: 3px solid #1e293b;
        position: relative;
        background: #fff;
        padding: 5px 6px 4px 6px;
        text-align: center;
        flex: 0 0 auto;
        margin-top: auto;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .presc-print-footer-notice {
        color: #d32f2f;
        font-size: 10.5px;
        font-weight: 700;
    }
    .presc-footer-accent-cyan {
        position: absolute;
        right: 0;
        top: -16px;
        width: 115px;
        height: 16px;
        background: #00c0ef;
        clip-path: polygon(25% 0%, 100% 0%, 100% 100%, 0% 100%);
    }
    .presc-footer-accent-dark {
        position: absolute;
        right: 0;
        top: -16px;
        width: 100px;
        height: 16px;
        background: #1e293b;
        clip-path: polygon(28% 0%, 100% 0%, 100% 100%, 0% 100%);
    }
    </style>
</head>
<body>

<!-- Full-height Prescription Pad Container -->
<div class="presc-pad-print">
    
    <!-- Dynamic Hospital Header Banner -->
    <div class="presc-header-banner">
        <div class="presc-header-top">
            <!-- Curved Decorative Shapes -->
            <svg class="presc-header-left-shapes" viewBox="0 0 140 56" preserveAspectRatio="none">
                <!-- Green curved ribbon -->
                <path d="M 0 0 L 140 0 C 120 25, 95 50, 58 56 L 0 56 Z" fill="#27ae60" />
                <!-- Cyan curved ribbon -->
                <path d="M 0 0 L 116 0 C 98 25, 78 50, 44 56 L 0 56 Z" fill="#00c0ef" />
                <!-- Navy logo backdrop -->
                <path d="M 0 0 L 92 0 C 78 25, 62 50, 32 56 L 0 56 Z" fill="#152836" />
            </svg>

            <!-- Dynamic Circular Logo Badge -->
            <div class="presc-logo-wrap">
                <?php if (!empty($h_logo_url)) { ?>
                    <div class="presc-logo-circle-container">
                        <img src="<?php echo $h_logo_url; ?>" alt="<?php echo html_escape($h_name); ?>" class="presc-logo-img">
                    </div>
                <?php } else { ?>
                    <svg width="44" height="44" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="47" fill="#ffffff" stroke="#004467" stroke-width="4"/>
                        <circle cx="50" cy="50" r="42" fill="#004467"/>
                        <circle cx="50" cy="50" r="32" fill="#ffffff"/>
                        <path id="textCircle" d="M 18,50 A 32,32 0 1,1 82,50" fill="none"/>
                        <text font-size="7.5" font-weight="bold" fill="#ffffff" letter-spacing="0.5">
                            <textPath href="#textCircle" startOffset="50%" text-anchor="middle">
                                <?php echo html_escape(strtoupper(mb_strimwidth($h_name, 0, 32, ''))); ?>
                            </textPath>
                        </text>
                        <g transform="translate(26, 38)">
                            <path d="M 5 12 L 8 4 L 11 12 L 14 4 L 17 12" stroke="#d32f2f" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            <text x="23" y="12" font-size="14" font-weight="900" font-style="italic" fill="#004467" font-family="Arial, sans-serif">max</text>
                            <path d="M 2 2 L 6 2 M 4 0 L 4 4" stroke="#d32f2f" stroke-width="1.5" stroke-linecap="round"/>
                        </g>
                        <text x="50" y="73" font-size="6.5" font-weight="bold" fill="#004467" text-anchor="middle" font-family="Arial, sans-serif">EST.-2024</text>
                    </svg>
                <?php } ?>
            </div>

            <!-- Dynamic Hospital Title Center/Right -->
            <div class="presc-header-title-wrap">
                <h1 class="presc-hospital-title"><?php echo html_escape($h_name); ?></h1>
            </div>
        </div>

        <!-- Dynamic Sub-Header Address & Contact -->
        <div class="presc-header-sub">
            <div class="presc-header-address"><?php echo html_escape($h_address); ?></div>
            <div class="presc-header-contact">
                <?php if (!empty($h_email)) { ?>
                    Gmail :- <?php echo html_escape($h_email); ?>
                <?php } ?>
                <?php if (!empty($h_email) && !empty($h_phone)) { ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                <?php } ?>
                <?php if (!empty($h_phone)) { ?>
                    (M) <?php echo html_escape($h_phone); ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Doctor & Patient Details Row -->
    <div class="presc-top-box-print">
        <!-- Left: Doctor Details -->
        <div class="presc-doc-col">
            <div class="presc-doc-title"><?php echo html_escape($doc_name); ?></div>
            <div class="presc-doc-qual-row">
                <div style="flex-shrink:0; width:26px; height:36px; display:flex; align-items:center; justify-content:center;">
                    <svg width="24" height="34" viewBox="0 0 64 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="6" r="4" fill="#004467"/>
                        <path d="M 32 14 C 20 6, 8 10, 2 20 C 12 20, 22 24, 30 28 C 26 22, 20 18, 12 16 C 22 16, 28 20, 32 24 C 36 20, 42 16, 52 16 C 44 18, 38 22, 34 28 C 42 24, 52 20, 62 20 C 56 10, 44 6, 32 14 Z" fill="#004467"/>
                        <rect x="30" y="8" width="4" height="66" rx="2" fill="#004467"/>
                        <polygon points="32,78 28,72 36,72" fill="#004467"/>
                        <path d="M 32 26 C 18 32, 18 42, 32 48 C 46 54, 46 64, 32 70" fill="none" stroke="#004467" stroke-width="3.5" stroke-linecap="round"/>
                        <path d="M 32 26 C 46 32, 46 42, 32 48 C 18 54, 18 64, 32 70" fill="none" stroke="#004467" stroke-width="3.5" stroke-linecap="round"/>
                        <circle cx="28" cy="24" r="2.5" fill="#004467"/>
                        <circle cx="36" cy="24" r="2.5" fill="#004467"/>
                    </svg>
                </div>
                <div class="presc-doc-qual">
                    <?php echo nl2br(html_escape($doc_qual)); ?>
                </div>
            </div>
            <?php if (!empty($doc_spec)) { ?>
            <div class="presc-doc-timing">
                <?php echo html_escape($doc_spec); ?>
            </div>
            <?php } ?>
        </div>

        <!-- Right: Patient Details -->
        <div class="presc-patient-col">
            <div class="presc-pat-line">
                <span class="presc-label">Patient Name</span>
                <span class="presc-dots-line"><?php echo !empty($result['patient_name']) ? html_escape($result['patient_name']) : ''; ?></span>
            </div>
            <div class="presc-pat-line">
                <span class="presc-label">Address</span>
                <span class="presc-dots-line"><?php echo !empty($result['address']) ? html_escape($result['address']) : ''; ?></span>
            </div>
            <div class="presc-pat-inline-row">
                <div class="presc-pat-inline-cell" style="flex: 1.1;">
                    <span class="presc-label">Age</span>
                    <span class="presc-dots-line"><?php echo ($compact_age !== '-') ? $compact_age : ''; ?></span>
                </div>
                <div class="presc-pat-inline-cell" style="flex: 1;">
                    <span class="presc-label">Sex</span>
                    <span class="presc-dots-line"><?php echo !empty($result['gender']) ? html_escape($result['gender']) : ''; ?></span>
                </div>
                <div class="presc-pat-inline-cell" style="flex: 1.4;">
                    <span class="presc-label">Date</span>
                    <span class="presc-dots-line"><?php echo $print_date_only; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Prescription Body: Fully expands along the red dividing line -->
    <div class="presc-print-body">
        <!-- Left Column: Vitals / Clinical Observations -->
        <div class="presc-print-left">
            <div class="presc-obs-item">
                <span class="presc-obs-label">O/E :</span>
            </div>
            <div class="presc-obs-item">
                <span class="presc-obs-label">BP</span>
                <span class="presc-obs-val"><?php echo !empty($result['bp']) ? html_escape($result['bp']) : ''; ?></span>
            </div>
            <div class="presc-obs-item">
                <span class="presc-obs-label">Pulse</span>
                <span class="presc-obs-val"><?php echo !empty($result['pulse']) ? html_escape($result['pulse']) : ''; ?></span>
            </div>
            <div class="presc-obs-item">
                <span class="presc-obs-label">Weight</span>
                <span class="presc-obs-val"><?php echo !empty($result['weight']) ? html_escape($result['weight']) : ''; ?></span>
            </div>
            <div class="presc-obs-item">
                <span class="presc-obs-label">Temp.</span>
                <span class="presc-obs-val"><?php echo !empty($result['temperature']) ? html_escape($result['temperature']) : ''; ?></span>
            </div>
            <div class="presc-obs-item">
                <span class="presc-obs-label">Investigation</span>
            </div>
        </div>

        <!-- Right Column: Rx Area -->
        <div class="presc-print-right">
            <div class="presc-rx-badge">R<sub>x</sub></div>
            <div style="flex: 1; height: 100%;"></div>
        </div>
    </div>

    <!-- Bottom Emergency Notice Footer -->
    <div class="presc-print-footer">
        <div class="presc-footer-accent-cyan"></div>
        <div class="presc-footer-accent-dark"></div>
        <div class="presc-print-footer-notice">
            বিঃদ্রঃ- রোগীর কোন এমার্জেন্সি অসুবিধা হলে আমাদের উপরে দেওয়া নম্বরে যোগাযোগ করুন
        </div>
    </div>
</div>

</body>
</html>
