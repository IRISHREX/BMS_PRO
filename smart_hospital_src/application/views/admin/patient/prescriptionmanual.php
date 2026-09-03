<?php
$currency_symbol = $this->customlib->getHospitalCurrencyFormat();

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

$print_date_only = date($this->customlib->getHospitalDateFormat(true, false));
?>

<style>
.presc-pad-container {
    font-family: 'Poppins', system-ui, -apple-system, sans-serif;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    margin: 6px 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 520px;
}

/* Dynamic Header Banner */
.presc-header-banner-modal {
    width: 100%;
    background: #fff;
    overflow: hidden;
    flex: 0 0 auto;
}
.presc-header-top-modal {
    background: #152836;
    display: flex;
    align-items: center;
    position: relative;
    min-height: 56px;
    max-height: 66px;
    overflow: hidden;
}
.presc-header-left-shapes-modal {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 150px;
    z-index: 1;
}
.presc-logo-wrap-modal {
    position: relative;
    z-index: 2;
    margin-left: 10px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}
.presc-logo-circle-container-modal {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #004467;
    box-shadow: 0 0 0 2px #00c0ef;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.presc-logo-img-modal {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 50%;
}
.presc-header-title-wrap-modal {
    flex: 1;
    text-align: center;
    padding-left: 55px;
    padding-right: 15px;
    z-index: 2;
    overflow: hidden;
}
.presc-hospital-title-modal {
    color: #ffc107;
    font-size: clamp(14px, 2.5vw, 18px);
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
.presc-header-sub-modal {
    background: #fff;
    border-bottom: 2.5px solid #00c0ef;
    padding: 5px 8px;
    text-align: center;
    color: #1e293b;
    line-height: 1.3;
    overflow: hidden;
}
.presc-header-address-modal {
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 2px;
    word-break: break-word;
    line-height: 1.25;
}
.presc-header-contact-modal {
    font-size: 11px;
    font-weight: 600;
    line-height: 1.25;
}

/* Top Doctor & Patient Box */
.presc-top-box {
    border-top: 2px solid #d32f2f;
    border-bottom: 2px solid #d32f2f;
    display: flex;
    background: #fff;
    flex: 0 0 auto;
}
.presc-doc-col {
    width: 44%;
    border-right: 2px solid #d32f2f;
    padding: 8px 12px;
    box-sizing: border-box;
}
.presc-doc-title {
    color: #d32f2f;
    font-size: 19px;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 4px;
}
.presc-doc-qual-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.presc-doc-qual-text {
    color: #004467;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.3;
    text-transform: uppercase;
}
.presc-doc-timing {
    color: #004467;
    font-size: 10.5px;
    font-weight: 700;
    text-align: center;
    margin-top: 5px;
}

/* Patient Column */
.presc-patient-col {
    width: 56%;
    padding: 8px 12px;
    box-sizing: border-box;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.presc-patient-line {
    display: flex;
    align-items: flex-end;
    margin-bottom: 6px;
}
.presc-patient-inline-group {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}
.presc-label {
    color: #0088cc;
    font-weight: 600;
    font-style: italic;
    white-space: nowrap;
    font-size: 12px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.presc-val-dots {
    border-bottom: 1.5px dotted #0088cc;
    flex-grow: 1;
    color: #0f172a;
    font-style: normal;
    font-weight: 600;
    padding-left: 6px;
    margin-left: 4px;
    min-height: 16px;
    line-height: 1.2;
}

/* Main Prescription Body */
.presc-body-layout {
    display: flex;
    flex: 1 1 auto;
    min-height: 380px;
    background: #fff;
}
.presc-left-col {
    width: 25%;
    min-width: 150px;
    border-right: 2px solid #d32f2f;
    padding: 12px 10px;
    background: #fff;
    box-sizing: border-box;
}
.presc-vital-item {
    margin-bottom: 22px;
    font-size: 12px;
}
.presc-vital-label {
    color: #0088cc;
    font-weight: 600;
}
.presc-vital-val {
    color: #1e293b;
    font-weight: 600;
    margin-left: 4px;
}
.presc-right-col {
    width: 75%;
    padding: 12px 16px;
    position: relative;
    background: #fff;
    flex: 1;
    box-sizing: border-box;
}
.presc-rx-badge {
    font-size: 30px;
    font-weight: bold;
    color: #d32f2f;
    font-family: 'Times New Roman', Times, serif;
    line-height: 1;
    margin-bottom: 12px;
}

/* Footer Section */
.presc-footer-section {
    border-top: 3px solid #1e293b;
    position: relative;
    background: #fff;
    padding: 5px 10px 4px 10px;
    text-align: center;
    flex: 0 0 auto;
    margin-top: auto;
}
.presc-footer-notice {
    color: #d32f2f;
    font-size: 11px;
    font-weight: 700;
}
.presc-footer-accent-cyan {
    position: absolute;
    right: 0;
    top: -18px;
    width: 125px;
    height: 18px;
    background: #00c0ef;
    clip-path: polygon(25% 0%, 100% 0%, 100% 100%, 0% 100%);
}
.presc-footer-accent-dark {
    position: absolute;
    right: 0;
    top: -18px;
    width: 108px;
    height: 18px;
    background: #1e293b;
    clip-path: polygon(28% 0%, 100% 0%, 100% 100%, 0% 100%);
}
</style>

<!-- Prescription Design Applied -->
<div class="presc-pad-container mx-2">
    <!-- Dynamic Header Banner -->
    <div class="presc-header-banner-modal">
        <div class="presc-header-top-modal">
            <!-- Curved Decorative Shapes -->
            <svg class="presc-header-left-shapes-modal" viewBox="0 0 150 58" preserveAspectRatio="none">
                <!-- Green curved ribbon -->
                <path d="M 0 0 L 150 0 C 130 26, 100 52, 60 58 L 0 58 Z" fill="#27ae60" />
                <!-- Cyan curved ribbon -->
                <path d="M 0 0 L 125 0 C 105 26, 85 52, 48 58 L 0 58 Z" fill="#00c0ef" />
                <!-- Navy logo backdrop -->
                <path d="M 0 0 L 100 0 C 85 26, 70 52, 35 58 L 0 58 Z" fill="#152836" />
            </svg>

            <!-- Dynamic Circular Logo Badge -->
            <div class="presc-logo-wrap-modal">
                <?php if (!empty($h_logo_url)) { ?>
                    <div class="presc-logo-circle-container-modal">
                        <img src="<?php echo $h_logo_url; ?>" alt="<?php echo html_escape($h_name); ?>" class="presc-logo-img-modal">
                    </div>
                <?php } else { ?>
                    <svg width="48" height="48" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="47" fill="#ffffff" stroke="#004467" stroke-width="4"/>
                        <circle cx="50" cy="50" r="42" fill="#004467"/>
                        <circle cx="50" cy="50" r="32" fill="#ffffff"/>
                        <path id="textCircleModal" d="M 18,50 A 32,32 0 1,1 82,50" fill="none"/>
                        <text font-size="7.5" font-weight="bold" fill="#ffffff" letter-spacing="0.5">
                            <textPath href="#textCircleModal" startOffset="50%" text-anchor="middle">
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
            <div class="presc-header-title-wrap-modal">
                <h1 class="presc-hospital-title-modal"><?php echo html_escape($h_name); ?></h1>
            </div>
        </div>

        <!-- Dynamic Sub-Header Address & Contact -->
        <div class="presc-header-sub-modal">
            <div class="presc-header-address-modal"><?php echo html_escape($h_address); ?></div>
            <div class="presc-header-contact-modal">
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

    <!-- Top Box: Dynamic Doctor Details (Left) & Patient Details (Right) -->
    <div class="presc-top-box">
        <!-- Left: Dynamic Doctor Details Section -->
        <div class="presc-doc-col">
            <div class="presc-doc-title">
                <?php echo html_escape($doc_name); ?>
            </div>
            <div class="presc-doc-qual-row">
                <div style="flex-shrink:0; width:26px; height:36px; display:flex; align-items:center; justify-content:center;">
                    <svg width="26" height="36" viewBox="0 0 64 80" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                <div class="presc-doc-qual-text">
                    <?php echo nl2br(html_escape($doc_qual)); ?>
                </div>
            </div>
            <?php if (!empty($doc_spec)) { ?>
            <div class="presc-doc-timing">
                <?php echo html_escape($doc_spec); ?>
            </div>
            <?php } ?>
        </div>

        <!-- Right: Patient Details Section -->
        <div class="presc-patient-col">
            <div class="presc-patient-line">
                <span class="presc-label">Patient Name</span>
                <span class="presc-val-dots"><?php echo !empty($result['patient_name']) ? html_escape($result['patient_name']) : ''; ?></span>
            </div>
            <div class="presc-patient-line">
                <span class="presc-label">Address</span>
                <span class="presc-val-dots"><?php echo !empty($result['address']) ? html_escape($result['address']) : ''; ?></span>
            </div>
            <div class="presc-patient-inline-group">
                <div style="flex:1; display:flex; align-items:flex-end;">
                    <span class="presc-label">Age</span>
                    <span class="presc-val-dots"><?php echo ($compact_age !== '-') ? $compact_age : ''; ?></span>
                </div>
                <div style="flex:1; display:flex; align-items:flex-end;">
                    <span class="presc-label">Sex</span>
                    <span class="presc-val-dots"><?php echo !empty($result['gender']) ? html_escape($result['gender']) : ''; ?></span>
                </div>
                <div style="flex:1.2; display:flex; align-items:flex-end;">
                    <span class="presc-label">Date</span>
                    <span class="presc-val-dots"><?php echo $print_date_only; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Prescription Body -->
    <div class="presc-body-layout">
        <!-- Left Column: Clinical Observations & Vitals -->
        <div class="presc-left-col">
            <div class="presc-vital-item">
                <span class="presc-vital-label">O/E :</span>
            </div>
            <div class="presc-vital-item">
                <span class="presc-vital-label">BP</span>
                <span class="presc-vital-val"><?php echo !empty($result['bp']) ? html_escape($result['bp']) : ''; ?></span>
            </div>
            <div class="presc-vital-item">
                <span class="presc-vital-label">Pulse</span>
                <span class="presc-vital-val"><?php echo !empty($result['pulse']) ? html_escape($result['pulse']) : ''; ?></span>
            </div>
            <div class="presc-vital-item">
                <span class="presc-vital-label">Weight</span>
                <span class="presc-vital-val"><?php echo !empty($result['weight']) ? html_escape($result['weight']) : ''; ?></span>
            </div>
            <div class="presc-vital-item">
                <span class="presc-vital-label">Temp.</span>
                <span class="presc-vital-val"><?php echo !empty($result['temperature']) ? html_escape($result['temperature']) : ''; ?></span>
            </div>
            <div class="presc-vital-item">
                <span class="presc-vital-label">Investigation</span>
            </div>
        </div>

        <!-- Right Column: Rx Area for Doctor -->
        <div class="presc-right-col">
            <div class="presc-rx-badge">R<sub>x</sub></div>
            <div class="presc-writing-area">
                <!-- Blank prescription space for doctor -->
            </div>
        </div>
    </div>

    <!-- Footer Notice Bar -->
    <div class="presc-footer-section">
        <div class="presc-footer-accent-cyan"></div>
        <div class="presc-footer-accent-dark"></div>
        <div class="presc-footer-notice">
            বিঃদ্রঃ- রোগীর কোন এমার্জেন্সি অসুবিধা হলে আমাদের উপরে দেওয়া নম্বরে যোগাযোগ করুন
        </div>
    </div>
</div>

<script type="text/javascript">
    function delete_prescription(id, opdid) {
        var msg = '<?php echo $this->lang->line('are_you_sure'); ?>';
        if (confirm(msg)) {
            $.ajax({
                url: '<?php echo base_url(); ?>admin/prescription/deletePrescription/' + id + '/' + opdid,
                success: function (res) {
                    window.location.reload(true);
                },
                error: function () {
                    alert("<?php echo $this->lang->line('fail'); ?>")
                }
            });
        }
    }
</script>
