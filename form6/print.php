<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =======================
// DATABASE CONNECTION
// =======================
$conn = new mysqli("localhost", "u155592346_usr_icthub", "+kuydZ4M", "u155592346_db_icthub");if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// =======================
// VALIDATE TOKEN
// =======================
if (empty($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
// =======================
// FETCH RECORD USING TOKEN
// =======================
$stmt = $conn->prepare(
    "SELECT * FROM form6_applicationforleave WHERE print_token = ? LIMIT 1"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Record not found.");
}

// ✅ FETCH FIRST
$data = $result->fetch_assoc();

$approvalEsign = [
    'admin_esign' => '',
    'unit_head_esign' => '',
    'unit_head_recommendation' => '',
    'approve_for_days_without_pay' => '',
    'approve_for_days_with_pay' => '',
    'approve_for_others' => '',
    'asds_sds_esign' => '',
    'disapproved_due_to' => '',
    'other_informations' => '',
];


/* =========================================
   UNIT HEAD + OFFICER INFO FETCH
========================================= */

$unitHeadData = [
    'unit_head_esign' => '',
    'unit_head_recommendation' => '',
    'signature_file' => '',
    'officer_name' => '',
    'officer_position' => ''
];

$stmt4 = $conn->prepare("
SELECT 
    u.unit_head_esign,
    u.unit_head_recommendation,
    a.officer_name,
    a.officer_position,
    a.signature_file
FROM form6_applicationforleave p

LEFT JOIN form6_applicationforleave_other_updates u
    ON u.parent_id = p.id

LEFT JOIN department_authorizers a
    ON TRIM(LOWER(a.department)) = TRIM(LOWER(p.department))

WHERE p.id = ?
ORDER BY u.id DESC
LIMIT 1
");

$stmt4->bind_param("i", $data['id']);
$stmt4->execute();
$res4 = $stmt4->get_result();

if ($row4 = $res4->fetch_assoc()) {
    $unitHeadData = $row4;
}

$stmt4->close();


/* =========================================
   CHECK IF UNIT HEAD SIGNED
========================================= */

$unitHeadEsignStatus = strtolower(trim($unitHeadData['unit_head_esign'] ?? ''));

$isUnitHeadSigned = in_array($unitHeadEsignStatus, [
    'approve & signed',
    'approved & signed',
    'approved',
    'signed'
]);

/* =========================================
   UNIT HEAD SIGNATURE PATH
========================================= */

$unitHeadSignaturePath = ($isUnitHeadSigned && !empty($unitHeadData['signature_file']))
    ? '../php/uploads/e_signatures/authorized personnels/' . $unitHeadData['signature_file']
    : '';

$recommendation = strtolower($unitHeadData['unit_head_recommendation'] ?? '');

$isApprovedRec = str_contains($recommendation, 'approval') 
                 && !str_contains($recommendation, 'disapproval');

$isDisapprovedRec = str_contains($recommendation, 'disapproval');


// var_dump($unitHeadData);


$stmt3 = $conn->prepare("
    SELECT *
    FROM form6_applicationforleave_other_updates
    WHERE parent_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$stmt3->bind_param("i", $data['id']); 
$stmt3->execute();
$res3 = $stmt3->get_result();

if ($res3->num_rows > 0) {
    $approvalEsign = $res3->fetch_assoc();
}
$adminEsignStatus = strtolower(trim($approvalEsign['admin_esign'] ?? ''));
$isAdminSigned = in_array($adminEsignStatus, [
    'approve & signed',
    'approved & signed',
    'approved',
    'signed'
]);

// ----------------------
// ASDS/SDS Status Check
// ----------------------
$asdsStatus = strtolower(trim($approvalEsign['asds_sds_esign'] ?? ''));
$isAsdsSigned = in_array($asdsStatus, [
    'approve & signed',
    'approved & signed',
    'approved',
    'signed'
]);

// ----------------------
// ASDS/SDS Name
// ----------------------
// Ensure $asdsNameRaw exists from your earlier fetch

$asdsNameRaw = $data['name_of_official'] ?? '';
$asdsName = strtolower(trim(preg_replace('/\s+/', ' ', $asdsNameRaw)));

// ----------------------
// ASDS/SDS Image Mapping
// ----------------------
$asdsSignatureMap = [
    'celedonio b. balderas jr.' => '../php/uploads/e_signatures/authorized personnels/sds_sampleSign.png',
    'herbert d. perez'         => '../php/uploads/e_signatures/authorized personnels/asds_sampleSign.png',
    'san mark a. morcoso'      => '../php/uploads/e_signatures/authorized personnels/sanmark_sampleSign.png',
];

// ----------------------
// Final Image Paths
// ----------------------
$asdsSignaturePath = ($isAsdsSigned && isset($asdsSignatureMap[$asdsName]))
    ? $asdsSignatureMap[$asdsName]
    : '';
$adminSignaturePath = $isAdminSigned
    ? '../php/uploads/e_signatures/authorized personnels/admin_sampleSign.png'
    : '';


$personnel = [
    'as_of' => '',
    'vacation_leave_total_earned' => '',
    'vacation_leave_less_this_application' => '',
    'vacation_leave_balance' => '',
    'sick_leave_total_earned' => '',
    'sick_leave_less_this_application' => '',
    'sick_leave_balance' => '',
];

$stmt2 = $conn->prepare("
    SELECT *
    FROM form6_applicationforleave_personnel_updates
    WHERE parent_id = ?
    ORDER BY submitted_at DESC
    LIMIT 1
");
$stmt2->bind_param("i", $data['id']); 
$stmt2->execute();
$res2 = $stmt2->get_result();

if ($res2->num_rows > 0) {
    $personnel = $res2->fetch_assoc();
}



// =======================
// NORMALIZE SAVED DATA
// =======================

// A. TYPE OF LEAVE
$selectedLeaveTypes = array_map(
    'trim',
    explode(',', $data['typeofleave_A'] ?? '')
);

// B. DETAILS OF LEAVE
$leaveDetails = $data['specification_of_leave'] ?? '';
$otherPurpose = $data['other_purpose'] ?? '';
$communication = $data['communication'] ?? '';

// =======================
// HELPER FUNCTIONS
// =======================
function isChecked($value, $array) {
    return in_array($value, $array) ? 'checked' : '';
}

function contains($needle, $haystack) {
    return stripos($haystack, $needle) !== false;
}

$withinPHText = '';

if (preg_match('/Within Philippines\s*:\s*([^;]+)/i', $leaveDetails, $matches)) {
    $withinPHText = trim($matches[1]); }

$abroadText = '';

if (preg_match('/Abroad\s*:\s*([^;]+)/i', $leaveDetails, $matches)) {
    $abroadText = trim($matches[1]); }

$inHospitalText = '';

if (preg_match('/In Hospital\s*:\s*([^;]+)/i', $leaveDetails, $matches)) {
    $inHospitalText = trim($matches[1]); }

$outPatientText = '';

if (preg_match('/Out Patient\s*:\s*([^;]+)/i', $leaveDetails, $matches)) {
    $outPatientText = trim($matches[1]); }

$specialLeaveBWText = '';

if (preg_match('/Special Leave \(Women\)\s*:\s*([^;]+)/i', $leaveDetails, $matches)) {
    $specialLeaveBWText = trim($matches[1]); 
}

$employeeSignature = '';

if (!empty($data['e_signature'])) {
    $employeeSignature = '../php/' . $data['e_signature'];
}




    

$conn->close();
// echo '<pre>';
// echo "ASDS Status: "; var_dump($approvalEsign['asds_sds_esign'] ?? 'NULL');
// echo "Is ASDS Signed? "; var_dump($isAsdsSigned);
// echo "ASDS Name: "; var_dump($asdsName ?? 'NULL');
// echo "Signature Path: "; var_dump($asdsSignaturePath);
// echo '</pre>';
// exit;

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print Form 6</title>

    <!-- Use your existing admin CSS -->
    <link rel="stylesheet" href="print.css">

    <style>
    @page {
        size: A4 portrait;
        margin: 0.05;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #fff;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
    </style>
</head>

<body onload="window.print()">


    <div class="print-wrapper">
        <!-- ===== PAGE 1 ===== -->
        <div class="form6-tag">
            <em>
                <p>Civil Service Form No. 6</p>
                <p>Revised 2020</p>
            </em>
        </div>
        <div class="department-header">
            <div class="department-logos-01">
                <img src="../Images/logo.png" alt="Logo 1" class="department-logo" />
            </div>
            <div class="department-title">
                <em class="header-title">
                    <p>Republic of the Philippines</p>
                    <p>(City Schools Division of the City of Tayabas)</p>
                    <p>(Brgy. Potol, Tayabas City)</p>
                </em>
                <h2 style="margin-bottom: 5px; font-size: 20px; text-transform: uppercase">
                    Application for Leave
                </h2>
            </div>
            <div class="stamp">
                <p>Stamp of Date Receipt</p>
            </div>
        </div>

        <div class="main-container" style="border: 1px solid; margin-left: 0; margin-right: 0">
            <div class="flex-input-wrapper" style="display: flex; border: 1px solid; padding: 4px">
                <div class="form-group printing-form-group" style="margin-bottom: 0; flex: 1">
                    <label for="department" style="
                  align-self: center;
                  width: 10rem;
                  text-transform: uppercase;
                ">1. Office/Department</label>
                    <input id="department" name="department" style="
                    font-weight: bold;
                    border-bottom: 1px solid;
                    border-top: 0;
                    border-left: 0;
                    border-right: 0;
                    border-radius: 0%;
                    padding: 1%;
                    " required value="<?= htmlspecialchars($data['department']) ?>" />
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1">
                    <label class="label" style="
                  align-self: center;
                  width: 10rem;
                  text-transform: uppercase;
                ">2. Name
                        <span style="font-size: 9px; text-transform: none">(Last Name)</span></label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" style="
                    font-weight: bold;
                    border-bottom: 1px solid;
                    border-top: 0;
                    border-left: 0;
                    border-right: 0;
                    border-radius: 0%;
                    min-width: 150px !important;
                    padding: 1%;
                    " required value="<?= htmlspecialchars($data['last_name']) ?>" />
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1">
                    <label class="label" style="font-size: 9px; text-transform: none">(First Name)</label>
                    <input type="text" id="first_name" name="first_name" style="
                    font-weight: bold;
                    border-bottom: 1px solid;
                    border-top: 0;
                    border-left: 0;
                    border-right: 0;
                    border-radius: 0%;
                    min-width: 150px !important;
                    padding: 1.5%;
                  " placeholder="First Name" required value="<?= htmlspecialchars($data['first_name']) ?>" />
                </div>
                <div class="form-group" style="margin-bottom: 0; flex: 1">
                    <label class="label" style="font-size: 9px; text-transform: none">(Middle Name)</label>
                    <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" style="
                  min-width: 150px !important;
                  font-weight: bold;      
                  border-bottom: 1px solid;
                  border-top: 0;
                  border-left: 0;
                  border-right: 0;
                  border-radius: 0%;
                  padding: 1.5%;
                " required value="<?= htmlspecialchars($data['middle_name']) ?>" />
                </div>
            </div>

            <div class="flex-input-wrapper" style="
              justify-content: space-between;
              border-bottom: 1px solid;
              border-left: 1px solid;
              border-right: 1px solid;
              display: flex;
              flex-wrap: wrap;
              gap: 1px !important;
              padding: 4px;
            ">
                <div class="form-group" style="display: flex; flex: 1; margin-bottom: 0">
                    <label class="label" for="date_of_filing" style="
                  align-self: center;
                  width: 10rem;
                  text-transform: uppercase;
                ">3. Date of Filing</label>
                    <input type="date" id="date_of_filing" name="date_of_filing" style="
                  border-bottom: 1px solid;
                  border-top: 0;
                  border-left: 0;
                  border-right: 0;
                  border-radius: 0%;
                  padding: 0%;
                  font-weight: bold;
                " placeholder="month/day/year" required value="<?= htmlspecialchars($data['date_of_filing']) ?>" />
                </div>
                <div class="form-group" style="display: flex; margin-bottom: 0; flex: 1">
                    <label class="label" for="position"
                        style="align-self: center; text-transform: uppercase">4.Position</label>
                    <input type="text" id="position" name="position" style="
                  border-bottom: 1px solid;
                  border-top: 0;
                  border-left: 0;
                  border-right: 0;
                  border-radius: 0%;
                  margin-left: 2px;
                  padding: 0%;
                  font-weight: bold;
                " placeholder="" required value="<?= htmlspecialchars($data['position']) ?>" />
                </div>
                <div class="form-group" style="display: flex; margin-bottom: 0; flex: 1">
                    <label class="label" for="salary" style="
                  align-self: center;
                  text-transform: uppercase;
                  width: 5rem;
                ">5.Salary</label>
                    <input type="text" id="salary" name="salary" style="
                  border-bottom: 1px solid;
                  border-top: 0;
                  border-left: 0;
                  border-right: 0;
                  border-radius: 0%;
                  padding: 0%;
                  font-weight: bold;
                " placeholder="" required value="<?= htmlspecialchars($data['salary']) ?>" />
                </div>
            </div>


            <div style="
              text-align: center;
              border-top: 2px solid;
              border-bottom: 2px solid;
              border-left: 1px solid;
              border-right: 1px solid;
            ">
                <p style="
                text-transform: uppercase;
                line-height: 0;
                padding: 0%;
                font-size: 12px;
                font-weight: 1000;
              ">
                    6. Details of Application
                </p>
            </div>
            <div class="types_of_leave">
                <div class="form-group formgroup_a" style="
                padding: 2px;
                border-top: 1px solid;
                margin-bottom: 0;
                border-bottom: 1px solid;
                border-left: 1px solid;
                border-right: 1px solid;
              ">
                    <label style="margin-bottom: 0">6. A. TYPE OF LEAVE BE AVAILED OF
                    </label>
                    <div class="checkbox-group" style="margin-top: 5px">
                        <div class="checkbox-option">
                            <label for="vacation_leave">
                                <input type="checkbox" id="vacation_leave" name="selectedOptions[]"
                                    value="Vacation Leave" <?= isChecked('Vacation Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Vacation Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No.
                                    292)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="mandatory_forced_leave">
                                <input type="checkbox" id="mandatory_forced_leave" name="selectedOptions[]"
                                    value="Mandatory / Forced Leave"
                                    <?= isChecked('Mandatory / Forced Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />

                                Mandatory/Forced Leave
                                <span style="font-size: 9px; font-weight: lighter">(Sec. 25, Rule XVI, Omnibus
                                    Rules
                                    Implementing E.O. No.
                                    292)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="sick-leave">
                                <input type="checkbox" id="sick-leave" name="selectedOptions[]" value="Sick Leave"
                                    <?= isChecked('Sick Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Sick Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No.
                                    292)
                                </span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="maternity_leave">
                                <input type="checkbox" id="maternity_leave" name="selectedOptions[]"
                                    value="Maternity Leave" <?= isChecked('Maternity Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Maternity Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)
                                </span>
                            </label>
                        </div>
                        <div class="checkbox-option">
                            <label for="paternity_leave">
                                <input type="checkbox" id="paternity_leave" name="selectedOptions[]"
                                    value="Paternity Leave" <?= isChecked('Paternity Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px" />
                                Paternity Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)
                                </span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="special_privilege_leave">
                                <input type="checkbox" id="special_privilege_leave" name="selectedOptions[]"
                                    value="Special Privilege Leave"
                                    <?= isChecked('Special Privilege Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Special Privilege Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No.
                                    292)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="solo_parent_leave">
                                <input type="checkbox" id="solo_parent_leave" name="selectedOptions[]"
                                    value="Solo Parent Leave" <?= isChecked('Solo Parent Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Solo Parent Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (RA No. 8972 / CSC MC No. 8, s. 2004)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="study_leave">
                                <input type="checkbox" id="study_leave" name="selectedOptions[]" value="Study Leave"
                                    <?= isChecked('Study Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Study Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No.
                                    292)
                                </span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="10day_VAWC_leave">
                                <input type="checkbox" id="10day_VAWC_leave" name="selectedOptions[]"
                                    value="10-Day VAWC Leave" <?= isChecked('10-Day VAWC Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                10-Day VAWC Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (RA No. 9262 / CSC MC No. 15, s. 2005)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="rehabilitation_privilege">
                                <input type="checkbox" id="rehabilitation_privilege" name="selectedOptions[]"
                                    value="Rehabilitation Privilege"
                                    <?= isChecked('Rehabilitation Privilege', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Rehabilitation Privilege
                                <span style="font-size: 9px; font-weight: lighter">
                                    (Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No.
                                    292)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="special_leave_benefits_for_women">
                                <input type="checkbox" id="special_leave_benefits_for_women" name="selectedOptions[]"
                                    value="Special Leave Benefits for Women"
                                    <?= isChecked('Special Leave Benefits for Women', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Special Leave Benefits for Women
                                <span style="font-size: 9px; font-weight: lighter">
                                    (RA No. 9710 / CSC MC No. 25, s. 2010)
                                </span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="special_emergency">
                                <input type="checkbox" id="special_emergency" name="selectedOptions[]"
                                    value="Special Emergency" <?= isChecked('Special Emergency', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Special Emergency
                                <span style="font-size: 9px; font-weight: lighter">
                                    (Calamity) Leave (CSC MC No. 2, s. 2012, as amended)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="adoption_leave">
                                <input type="checkbox" id="adoption_leave" name="selectedOptions[]"
                                    value="Adoption Leave" <?= isChecked('Adoption Leave', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Adoption Leave
                                <span style="font-size: 9px; font-weight: lighter">
                                    (R.A. No. 8552)</span>
                            </label>
                        </div>

                        <div class="checkbox-option">
                            <label for="others">
                                <input type="checkbox" id="others" name="selectedOptions[]" value="Others"
                                    <?= isChecked('Others', $selectedLeaveTypes) ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Others:
                                <input class="others_text_wrapper" type="text" id="others_text" name="others_text"
                                    value="<?= htmlspecialchars($data['other_type_of_leave'] ?? '') ?>"
                                    <?= isChecked('Others', $selectedLeaveTypes) ? '' : 'disabled' ?> style="
                                        border-bottom: 1px solid;
                                        border-left: 0;
                                        border-top: 0;
                                        border-right: 0;
                                        border-radius: 0%;
                                        padding: 1%;
                                    " disabled />
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group formgroup_b" style="
                    padding: 2px;
                    border-top: 1px solid;
                    border-bottom: 1px solid;
                    border-left: 1px solid;
                    border-right: 1px solid;
                ">
                    <label>B. DETAILS OF LEAVE</label>
                    <div class="checkbox-group">
                        <label style="margin-top: -1rem"><em> In case of Vacation/Special Privilege
                                Leave:</em></label>
                        <div class="checkbox-option b-check-options">
                            <label for="Within the Philippines">
                                <input type="checkbox" id="within_ph" name="specificationOptions[]"
                                    value="Within the Philippines"
                                    <?= contains('Within Philippines', $leaveDetails) ? 'checked' : '' ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Within the Philippines
                            </label>
                            <input class="input_text" type="text" id="within_ph_text" name="within_ph_text"
                                value="<?= htmlspecialchars($withinPHText) ?>"
                                <?= $withinPHText === '' ? 'disabled' : '' ?> style="
                                border-bottom: 1px solid;
                                border-top: 0;
                                border-left: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 1%;
                                " disabled />
                        </div>

                        <div class="checkbox-option b-check-options" style="align-content: center">
                            <label for="Abroad">
                                <input type="checkbox" id="abroad" name="specificationOptions[]" value="Abroad"
                                    <?= contains('Abroad', $leaveDetails) ? 'checked' : '' ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Abroad (Specify)
                            </label>
                            <input class="input_text" type="text" id="abroad_text" name="abroad_text"
                                value="<?= htmlspecialchars($abroadText) ?>" <?= $abroadText === '' ? 'disabled' : '' ?>
                                style="
                                border-bottom: 1px solid;
                                border-top: 0;
                                border-left: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 1%;
                                " disabled />
                        </div>
                        <label><em>In case of Sick Leave:</em></label>
                        <div class="checkbox-option b-check-options">
                            <label for="In Hospital">
                                <input type="checkbox" id="in_hospital" name="specificationOptions[]"
                                    value="In Hospital" <?= contains('In Hospital', $leaveDetails) ? 'checked' : '' ?>
                                    style="
                                    border-bottom: 1px solid;
                                    border-top: 0;
                                    border-left: 0;
                                    border-right: 0;
                                    border-radius: 0%;
                                    padding: 2%;
                                    margin-right: 0px;
                                    margin-top: 0;
                                " />
                                In Hospital (Specify Illness)
                            </label>
                            <input class="input_text" type="text" id="in_hospital_text" name="in_hospital_text"
                                value="<?= htmlspecialchars($inHospitalText) ?>"
                                <?= $inHospitalText === '' ? 'disabled' : '' ?> style="
                                border-bottom: 1px solid;
                                border-top: 0;
                                border-left: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 1%;
                                " disabled />
                        </div>

                        <div class="checkbox-option b-check-options">
                            <label for="Out Patient">
                                <input type="checkbox" id="out_patient" name="specificationOptions[]"
                                    value="Out Patient" <?= contains('Out Patient', $leaveDetails) ? 'checked' : '' ?>
                                    style="
                                    border-bottom: 1px solid;
                                    border-top: 0;
                                    border-left: 0;
                                    border-right: 0;
                                    border-radius: 0%;
                                    padding: 2%;
                                    margin-right: 0px;
                                    margin-top: 0;
                                " />
                                Out Patient (Specify Illness)
                            </label>
                            <input class="input_text" type="text" id="out_patient_text" name="out_patient_text"
                                value="<?= htmlspecialchars($outPatientText) ?>"
                                <?= $outPatientText === '' ? 'disabled' : '' ?> style="
                                border-bottom: 1px solid;
                                border-top: 0;
                                border-left: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 1%;
                                " disabled />
                        </div>

                        <label><em> In case of Special Leave Benefits for Women:</em></label>
                        <div class="checkbox-option b-check-options">
                            <label for="SpecialLeave-BW">
                                (Specify Illness)
                                <input class="input_text" type="text" id="special_leave_BW_spec"
                                    value="<?= htmlspecialchars($specialLeaveBWText) ?>"
                                    <?= $specialLeaveBWText === '' ? 'disabled' : '' ?> name="special_leave_BW_spec"
                                    style="
                                    border-bottom: 1px solid;
                                    border-top: 0;
                                    border-left: 0;
                                    border-right: 0;
                                    border-radius: 0%;
                                    padding: 1%;
                                    color: black;
                                " disabled />
                            </label>
                        </div>
                        <label><em>In case of Study Leave:</em></label>
                        <div class="checkbox-option b-check-options">
                            <label for="Completion of Master's Degree">
                                <input type="checkbox" id="completion_of_masters_degree"
                                    name="completion_of_masters_degree" value="Completion of Master's Degree"
                                    <?= contains('Completion of Master\'s Degree', $leaveDetails) ? 'checked' : '' ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Completion of Master's Degree
                            </label>
                        </div>
                        <div class="checkbox-option b-check-options">
                            <label for="BAR/Board Examination Review">
                                <input type="checkbox" id="BAR_Board_exam" name="BAR_Board_exam"
                                    value="BAR/Board Examination Review"
                                    <?= contains('BAR/Board Examination Review', $leaveDetails) ? 'checked' : '' ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                BAR/Board Examination Review
                            </label>
                        </div>

                        <label><em>Other purpose:</em></label>
                        <div class="checkbox-option b-check-options">
                            <label for="Monetization of Leave Credits">
                                <input type="checkbox" id="monetization_of_leave_credits" name="otherPurpose[]"
                                    value="Monetization of Leave Credits"
                                    <?= contains('Monetization of Leave Credits', $otherPurpose) ? 'checked' : '' ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Monetization of Leave Credits
                            </label>
                        </div>
                        <div class="checkbox-option b-check-options">
                            <label for="Terminal Leave">
                                <input type="checkbox" id="terminal_leave" name="otherPurpose[]" value="Terminal Leave"
                                    <?= contains('Terminal Leave', $otherPurpose) ? 'checked' : '' ?>
                                    style="margin-right: 0px; margin-top: 0" />
                                Terminal Leave
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display: flex">
                <div class="form-group" style="
                    width: 55%;
                    border: 1px solid;
                    padding: 5px;
                    margin-bottom: 0;
                ">
                    <label>6. C. NUMBER OF WORKING DAYS APPLIED FOR</label>
                    <div class="checkbox-option" style="margin-bottom: 0">
                        <input type="text" id="number_of_days_applied" name="number_of_days_applied"
                            value="<?= htmlspecialchars($data['number_of_days_applied']) ?>" style="
                            width: 300px;
                            margin-bottom: 10px;
                            border-bottom: 1px solid;
                            border-left: 0;
                            border-top: 0;
                            border-right: 0;
                            border-radius: 0%;
                            padding: 1%;
                            color:#000;
                            " disabled />
                        <label>INCLUSIVE DAYS</label>
                        <div class="checkbox-option" style="margin-bottom: 0">
                            <input type="text" id="inclusive_days" name="inclusive_days"
                                value="<?= htmlspecialchars($data['inclusive_days']) ?>" style="
                                width: 300px;
                                border-bottom: 1px solid;
                                border-left: 0;
                                border-top: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 1%;
                                color:#000;
                                " disabled />
                        </div>
                    </div>
                </div>

                <div class="form-group d-container-right" style="border: 1px solid; padding: 5px">
                    <label>6. D. COMMUTATION</label>
                    <div class="checkbox-option">
                        <label>
                            <input type="checkbox" id="not_required" name="communication[]" value="Not Required"
                                <?= contains('Not Required', $communication) ? 'checked' : '' ?>
                                style="margin-right: 2px; margin-top: 0" />
                            Not Required</label>

                        <div class="checkbox-option">
                            <label>
                                <input type="checkbox" id="required" name="communication[]" value="Required"
                                    <?= contains('Required', $communication) ? 'checked' : '' ?>
                                    style="margin-right: 2px" />

                                Required</label>
                        </div>
                    </div>
                    <div class="e-sign-container" style="margin-top: -3rem">
                        <div class="e-sign-input">
                            <div style="
                      width: 200px;
                      height: 100px;
                      display: flex;
                      justify-content: center; /* center horizontally */
                      align-items: center; /* center vertically */
                      align-self: center;
                      justify-self: center;
                      overflow: hidden;
                      margin-top: 0rem;
                      border: none;
                      z-index: 2;
                    ">
                                <img id="signature_image" src="<?= htmlspecialchars($employeeSignature) ?>"
                                    alt="Employee Signature" style="
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                      " />
                            </div>
                            <label style="text-align: center; margin-top: -1rem">(Signature of
                                Applicant)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="
              text-align: center;
              border-top: 2px solid;
              border-bottom: 2px solid;
              border-left: 1px solid;
              border-right: 1px solid;
            ">
                <p style="
                    text-transform: uppercase;
                    line-height: 0;
                    padding: 0%;
                    font-size: 12px;
                    font-weight: 1000;
                ">
                    7. Details of Action on Application
                </p>
            </div>

            <div style="display: flex">
                <div class="form-group" style="
                width: 55%;
                border: 1px solid;
                padding: 4px;
                margin-bottom: 0;
              ">
                    <form id="leave_form" method="POST" action="php/F6submit_personnel_update_leave.php" type="submit">
                        <input type="hidden" id="form_id" name="form6_applicationforleave" />

                        <label>7. A. CERTIFICATION OF LEAVE CREDITS</label>
                        <div class="checkbox-option" style="
                            display: flex;
                            justify-content: center;
                            margin-bottom: 3px;
                        ">
                            <label style="align-self: center">As of </label>

                            <input type="text" id="as_of" name="as_of" value="<?= e($personnel['as_of']) ?>" style="
                                width: 300px;
                                border-bottom: 1px solid;
                                border-left: 0;
                                border-top: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 2px 5px;
                                color:#000;
                            " disabled />
                            <?= $personnel['as_of'] === '' ? '' : '' ?>
                        </div>
                        <table style="
                            width: 80%;
                            font-size: 11px;
                            align-self: center;
                            justify-self: center;
                            text-align: center;
                            padding: 0;
                        ">
                            <tr>
                                <th style="border: 1px solid; width: 40%"></th>
                                <th style="border: 1px solid">Vacation Leave</th>
                                <th style="border: 1px solid">Sick Leave</th>
                            </tr>
                            <tr>
                                <td style="border: 1px solid"><em>Total Earned</em></td>
                                <td class="total-earned-col-1" style="border: 1px solid">
                                    <input type="text" id="vacation_leave_total_earned"
                                        name="vacation_leave_total_earned"
                                        value="<?= e($personnel['vacation_leave_total_earned']) ?>" style="
                                        width: 100%;
                                        border-bottom: 0;
                                        border-left: 0;
                                        border-top: 0;
                                        border-right: 0;
                                        border-radius: 0%;
                                        padding: 0%;
                                        text-align: center;
                                        color:#000;
                                        " disabled />
                                    <?= $personnel['vacation_leave_total_earned'] === '' ? '' : '' ?>
                                </td>
                                <td class="total-earned-col-2" style="border: 1px solid">
                                    <input type="text" id="sick_leave_total_earned" name="sick_leave_total_earned"
                                        value="<?= e($personnel['sick_leave_total_earned']) ?>" style="
                                        width: 100%;
                                        border-bottom: 0;
                                        border-left: 0;
                                        border-top: 0;
                                        border-right: 0;
                                        border-radius: 0%;
                                        padding: 0%;
                                        text-align: center;
                                        color:#000;
                                        " disabled />
                                    <?= $personnel['sick_leave_total_earned'] === '' ? '' : '' ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid">
                                    <em>Less this application</em>
                                </td>
                                <td class="less-col-1" style="border: 1px solid">
                                    <input type="text" id="vacation_leave_less_this_application"
                                        name="vacation_leave_less_this_application"
                                        value="<?= e($personnel['vacation_leave_less_this_application']) ?>" style="
                                        width: 100%;
                                        border-bottom: 0;
                                        border-left: 0;
                                        border-top: 0;
                                        border-right: 0;
                                        border-radius: 0%;
                                        padding: 0%;
                                        text-align: center;
                                        color:#000;
                                        " disabled />
                                    <?= $personnel['vacation_leave_less_this_application'] === '' ? '' : '' ?>
                                </td>
                                <td class="less-col-2" style="border: 1px solid">
                                    <input type="text" id="sick_leave_less_this_application"
                                        name="sick_leave_less_this_application"
                                        value="<?= e($personnel['sick_leave_less_this_application']) ?>" style="
                                        width: 100%;
                                        border-bottom: 0;
                                        border-left: 0;
                                        border-top: 0;
                                        border-right: 0;
                                        border-radius: 0%;
                                        padding: 0%;
                                        text-align: center;
                                        color:#000;
                                        " disabled />
                                    <?= $personnel['sick_leave_less_this_application'] === '' ? '' : '' ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid"><em>Balance</em></td>
                                <td class="balance-col-1" style="border: 1px solid">
                                    <input type="text" id="vacation_leave_balance" name="vacation_leave_balance"
                                        value="<?= e($personnel['vacation_leave_balance']) ?>" style="
                                        width: 100%;
                                        border-bottom: 0;
                                        border-left: 0;
                                        border-top: 0;
                                        border-right: 0;
                                        border-radius: 0%;
                                        padding: 0%;
                                        text-align: center;
                                        color:#000;
                                        " disabled />
                                    <?= $personnel['vacation_leave_balance'] === '' ? '' : '' ?>
                                </td>
                                <td class="balance-col-2" style="border: 1px solid">
                                    <input type="text" id="sick_leave_balance" name="sick_leave_balance"
                                        value="<?= e($personnel['sick_leave_balance']) ?>" style="
                                    width: 100%;
                                    border-bottom: 0;
                                    border-left: 0;
                                    border-top: 0;
                                    border-right: 0;
                                    border-radius: 0%;
                                    padding: 0%;
                                    text-align: center;
                                    color:#000;
                                    " disabled />
                                    <?= $personnel['sick_leave_balance'] === '' ? '' : '' ?>
                                </td>
                            </tr>
                        </table>
                        <button class="personnel-update" style="
                            display: none;
                            position: relative;
                            left: 53.5%;
                            margin-top: 5px;
                            border-radius: 4px;
                            border: none;
                            padding: 6px 10px;
                            cursor: pointer;
                            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.25);
                        " type="button">
                            Update Leave Balances
                        </button>
                    </form>
                    <form id="leave_form_admin_update" method="POST" action="php/F6submit_admin_update_leave.php"
                        type="submit">
                        <input type="hidden" id="form_id" name="parent_id" />
                        <div class="e-sign-container" style=" 
                            display: block;
                            margin-top: 10px;
                            align-self: center;
                            justify-self: center;
                            text-align: center; ">

                            <?php if (!empty($adminSignaturePath)) : ?>

                            <div id="admin_esign_wrapper" class="admin_esign_wrapper" style="
                                width: 200px;
                                height: 70px;
                                justify-content: center;
                                align-items: center;
                                margin-top: -3.5rem;
                                margin-left: 6rem;
                                border: none;
                                z-index: 2;
                                <?= $isAdminSigned ? '' : 'display:none;' ?>">
                                <img id="admin_signature_image"
                                    src="../php/uploads/e_signatures/authorized personnels/admin_sampleSign.png"
                                    alt="Admin Unit Signature" style="
                                        max-width: 100%;
                                        max-height: 100%;
                                        object-fit: contain;
                                        position: relative;
                                        top: 20px;" />
                            </div>
                            <?php endif; ?>
                            <div class="e-sign-input">
                                <p style="
                                    text-transform: uppercase;
                                    font-size: 12px;
                                    font-weight: 600;
                                    margin: 0;
                                    padding: 0;
                                ">
                                    Conrado C. Gabarda
                                </p>
                                <p style="font-size: 11px; margin: 0; padding: 0">
                                    Administrative Officer V.
                                </p>

                                <hr style="margin: 2px 0; justify-self: center; width: 50%" />
                                <label style="text-align: center">(Authorize Officer)</label>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="form-group d-container-right" style="border: 1px solid; padding: 4px">
                    <form id="leave_form_unit_head_update" method="POST" action="php/F6submit_unithead_update.php"
                        type="submit">
                        <input type="hidden" id="form_id" name="parent_id" />
                        <label>7. B. RECOMMENDATION</label>
                        <div class="checkbox-option">
                            <label>
                                <input id="approval_checkbox" name="unit_head_recommendation" type="checkbox"
                                    value="For Approval" style="margin-right: 2px"
                                    <?= $isApprovedRec ? 'checked' : '' ?> />
                                For Approval</label>

                            <div class="checkbox-option">
                                <label>
                                    <input id="disapprove_checkbox" name="unit_head_recommendation" type="checkbox"
                                        value="For disapproval due to" style="margin-right: 2px"
                                        <?= $isDisapprovedRec ? 'checked' : '' ?> />

                                    For disapproval due to</label>
                                <textarea type="text" id="disapproval_due_to" name="disapproval_due_to" style="
                                    width: 90%;
                                    min-height: 55px;
                                    margin-left: 1.6rem;
                                    border-bottom: 1px solid #000;
                                    border-top: 0;
                                    border-left: 0;
                                    border-right: 0;
                                    border-radius: 0;
                                    padding: 2px 4px;
                                    font-family: inherit;
                                    font-size: 10pt;
                                    white-space: normal;
                                    overflow-wrap: break-word;
                                    <?= !$isDisapprovedRec ? 'disabled' : '' ?>
                                "><?= e($unitHeadData['unit_head_recommendation']) ?></textarea>

                            </div>

                        </div>
                        <div class="e-sign-container" style="display: block">
                            <div id="sign-preview-wrapper" style="
                                width: 150px;
                                height: 100px;
                                justify-content: center;
                                align-items: center;
                                align-self: center;
                                justify-self: center;
                                margin-top: -5rem;
                                overflow: visible;
                               display: <?= $unitHeadSignaturePath ? 'flex' : 'none' ?>;
                            ">

                                <?php if ($unitHeadSignaturePath): ?>
                                <img id="unit-head-esign" src="<?= e($unitHeadSignaturePath) ?>"
                                    alt="Unit head Signature" style="
                                    max-width: 100%;
                                    max-height: 100%;
                                    object-fit: contain;
                                    position: relative;
                                    bottom: -30px;
                                    display: block;

                                " />
                                <?php endif; ?>
                            </div>
                            <div class="e-sign-input" style="width: 100%">
                                <?php if (!empty($unitHeadData['officer_name'])): ?>
                                <p id="nameOfRecommendingOfficial" style="
                                    text-transform: uppercase;
                                    font-size: 12px;
                                    font-weight: 600;
                                    margin: 0;
                                    padding: 0;
                                "> <?= strtoupper(e($unitHeadData['officer_name'])) ?></p>


                                <p id="positionOfRecommendingOfficial" style="font-size: 11px; margin: 0; padding: 0">
                                    <?= e($unitHeadData['officer_position']) ?>
                                </p>
                                <?php endif; ?>

                                <hr style="margin: 2px 0; justify-self: center; width: 50%" />
                                <label style="text-align: center">(Authorize Officer)</label>

                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <form id="leave_form_asds_sds_update" method="POST" action="php/F6submit_asds_sds_update.php" type="submit">
                <input type="hidden" id="form_id" name="parent_id" />

                <div class="form-group-approved-for" style="
                    justify-content: center;
                    border-top: 2px solid;
                    border-left: 1px solid;
                    border-right: 1px solid;
                    margin-bottom: 0;
                    padding: 5px;
                    display: flex;
                ">
                    <div class="form-group" style="width: 63%; margin-bottom: 0">
                        <label>7.C. APPROVED FOR:</label>
                        <div class="checkbox-option" style="
                            display: flex;
                            justify-content: left;
                            margin-bottom: 0;
                            margin-left: 1.5rem;
                        ">
                            <input type="text" id="daysWithPay" name="approve_for_days_with_pay" value="<?= htmlspecialchars($approvalEsign['approve_for_days_with_pay']) ?>
                            " style=" 
                            width: 70px;
                            border-bottom: 1px solid;
                            border-left: 0;
                            border-top: 0;
                            border-right: 0;
                            border-radius: 0%;
                            padding: 2px 5px;
                            " />
                            <label style="align-self: center">days with pay </label>
                        </div>

                        <div class="checkbox-option" style="
                            display: flex;
                            justify-content: left;
                            margin-bottom: 0;
                            margin-left: 1.5rem;
                        ">
                            <input type="text" id="daysWithoutPay" name="approve_for_days_without_pay" value="<?= htmlspecialchars($approvalEsign['approve_for_days_without_pay']) ?>
                                " style="
                                width: 70px;
                                border-bottom: 1px solid;
                                border-left: 0;
                                border-top: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 2px 5px;
                            " />
                            <label style="align-self: center">days without pay </label>
                        </div>

                        <div class="checkbox-option" style="
                            display: flex;
                            justify-content: left;
                            margin-bottom: 0;
                            margin-left: 1.5rem;
                        ">
                            <input type="text" id="approveForOthers" name="approve_for_others" value="<?= htmlspecialchars($approvalEsign['approve_for_others']) ?>
                            " style="
                              width: 70px;
                              border-bottom: 1px solid;
                              border-left: 0;
                              border-top: 0;
                              border-right: 0;
                              border-radius: 0%;
                              padding: 2px 5px;
                              " />
                            <label style="align-self: center">others (specify) </label>
                        </div>
                    </div>

                    <div class="form-group-disapproved-for" style="width: 50%; margin-bottom: 0">
                        <label>7.D. DISAPPROVED DUE TO:</label>
                        <div class="checkbox-option" style="
                            display: flex;
                            justify-content: left;
                            margin-bottom: 0;
                            margin-left: 1.5rem;
                        ">
                            <textarea id="disapprovedDueTo" name="disapproved_due_to" style="
                                width: 100%;
                                min-height: 55px;
                                border-bottom: 1px solid;
                                border-left: 0;
                                border-top: 0;
                                border-right: 0;
                                border-radius: 0%;
                                padding: 2px 5px;
                                ">
                                <?= htmlspecialchars($approvalEsign['disapproved_due_to']) ?>
                            </textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group-asds-sds-esign" style="
                    justify-content: center;
                    text-align: center;
                    border-bottom: 1px solid;
                    border-left: 1px solid;
                    border-right: 1px solid;
                    margin-bottom: 0;
                    padding: 4px;
                    overflow: visible;
                ">
                    <div style="
                        justify-content: center;
                        text-align: center;
                        overflow: visible;
                    ">
                        <div id="e-sign-input-top-management" style="
                                margin-bottom: -50px;
                                <?= empty($asdsSignaturePath) ? 'display:none;' : 'display:block;' ?>
                            ">
                            <div id="sign-preview-wrapper" style="
                                    width: 200px;
                                    height: 130px;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    margin-top: -5rem;
                                    overflow: visible;
                                ">
                                <img id="top-management-esign" src="<?= htmlspecialchars($asdsSignaturePath) ?>"
                                    alt="ASDS/SDS Signature" style="
                                        max-width: 100%;
                                        max-height: 100%;
                                        object-fit: contain;
                                        position: relative;
                                        margin-left: 35rem;
                                    " />
                            </div>
                        </div>


                        <div class="checkbox-option" style="
                            margin: 0;
                            padding: 0;
                            border: 0.5px solid;
                            width: 300px;
                            text-align: center;
                            justify-self: center;
                            align-self: center;
                            background-color: transparent;
                        ">
                            <input type="text" id="name_of_official" name="name_of_official"
                                value="<?= htmlspecialchars($data['name_of_official']) ?>" disabled style="
                                text-align: center;
                                border: 0;
                                border-radius: 0%;
                                font-weight: bolder;
                                padding: 4px;
                                text-transform: uppercase;
                                background-color: transparent;
                                " placeholder="Full Name" />

                            <div class="checkbox-option">
                                <input type="text" id="signatory_position" name="signatory_position"
                                    value="<?= htmlspecialchars($data['signatory_position']) ?>" disabled style="
                                    text-align: center;
                                    border-left: 0;
                                    border-top: 0;
                                    border-right: 0;
                                    border-radius: 0%;
                                    border-bottom: 0;
                                    padding: 0;
                                    margin: 0;
                                    background-color: transparent;
                                " placeholder="Position" />
                            </div>
                        </div>

                        <hr style="width: 250px; margin-top: 4px; margin-bottom: 2px" />
                        <label style="text-align: center; font-weight: bold">(Authorize Official)</label>
                    </div>

                </div>
            </form>
        </div>




        <!-- ===== PAGE BREAK ===== -->
        <div style="page-break-before: always;"></div>

        <!-- ===== PAGE 2 (INSTRUCTIONS) ===== -->
        <div class="page-two">
            <div style="margin-bottom: 0; border: 1px solid;">
                <p style="text-align:center; padding: 0px; margin: 5px 0; font-weight: 600;">INSTRUCTIONS AND
                    REQUIREMENTS</p>
            </div>
            <div style="display: flex; margin-top: 0; font-size: 11px">
                <div style="width: 49%; margin-right: .5rem;">
                    <p style="margin: 10px 0">
                        Application for any type of leave shall <span
                            style="font-weight: 600;border-bottom: 1px solid;">be made on this Form and to be
                            accomplished at least in duplicate</span> with documentary requirements, as
                        follows:
                    </p>

                    <p style="font-weight: 600; margin-bottom: 5px">
                        1. Vacation leave*
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        It shall be filed five (5) days in advance, whenever possible, of the
                        effective date of such leave. Vacation leave within in the Philippines or
                        abroad shall be indicated in the form for purposes of securing travel
                        authority and completing clearance from money and work
                        ccountabilities.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        2. Mandatory/Forced leave
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        Annual five-day vacation leave shall be forfeited if not taken during the
                        year. In case the scheduled leave has been cancelled in the exigency
                        of the service by the head of agency, it shall no longer be deducted from
                        the accumulated vacation leave. Availment of one (1) day or more
                        Vacation Leave (VL) shall be considered for complying the
                        mandatory/forced leave subject to the conditions under Section 25, Rule
                        XVI of the Omnibus Rules Implementing E.O. No. 292.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        3. Sick leave*
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         It shall be filed immediately upon employee's return from such leave.
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                         If filed in advance or exceeding five (5) days, application shall be
                        accompanied by a medical certificate. In case medical consultation
                        was not availed of, an affidavit should be executed by an applicant.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        4. Maternity leave* – 105 days
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Proof of pregnancy e.g. ultrasound, doctor’s certificate on the
                        expected date of delivery
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Accomplished Notice of Allocation of Maternity Leave Credits (CS
                        Form No. 6a), if needed
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                         Seconded female employees shall enjoy maternity leave with full pay
                        in the recipient agency.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        5. Paternity leave – 7 days
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        Proof of child’s delivery e.g. birth certificate, medical certificate and
                        marriage contract
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        6. Special Privilege leave – 3 days</p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        It shall be filed/approved for at least one (1) week prior to availment,
                        except on emergency cases. Special privilege leave within the
                        Philippines or abroad shall be indicated in the form for purposes of
                        securing travel authority and completing clearance from money and work
                        accountabilities.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        7. Solo Parent leave – 7 days
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        It shall be filed in advance or whenever possible five (5) days before
                        going on such leave with updated Solo Parent Identification Card.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        8. Study leave* – up to 6 months
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Shall meet the agency’s internal requirements, if any;
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                         Contract between the agency head or authorized representative and
                        the employee concerned.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        9. VAWC leave – 10 days
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         It shall be filed in advance or immediately upon the woman
                        employee’s return from such leave.
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         It shall be accompanied by any of the following supporting documents:
                    </p>
                    <p style="margin-bottom: 5px; margin-left: 1.5rem;">
                        a. Barangay Protection Order (BPO) obtained from the barangay;
                    </p>
                    <p style="margin-bottom: 5px; margin-left: 1.5rem;">
                        b. Temporary/Permanent Protection Order (TPO/PPO) obtained from
                        the court;
                    </p>
                    <p style="margin-bottom: 10px; margin-left: 1.5rem;">
                        c. If the protection order is not yet issued by the barangay or the court,
                        a certification issued by the Punong Barangay/Kagawad or
                        Prosecutor or the Clerk of Court that the application for the BPO,______________________
                    </p>
                </div>

                <div style="width: 49%; margin-top: .8rem; ">
                    <p style="margin-bottom: 10px; margin-left: 1.5rem; ">
                        TPO or PPO has been filed with the said office shall be sufficient
                        to support the application for the ten-day leave; or
                    </p>
                    <p style="margin-bottom: 10px; margin-left: 1.5rem;">
                        d. In the absence of the BPO/TPO/PPO or the certification, a police
                        report specifying the details of the occurrence of violence on the
                        victim and a medical certificate may be considered, at the
                        discretion of the immediate supervisor of the woman employee
                        concerned.
                    </p>

                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        10. Rehabilitation leave* – up to 6 months
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Application shall be made within one (1) week from the time of the
                        accident except when a longer period is warranted.
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Letter request supported by relevant reports such as the police
                        report, if any,
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Medical certificate on the nature of the injuries, the course of
                        treatment involved, and the need to undergo rest, recuperation, and
                        rehabilitation, as the case may be
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                         Written concurrence of a government physician should be obtained
                        relative to the recommendation for rehabilitation if the attending
                        physician is a private practitioner, particularly on the duration of the
                        period of rehabilitation.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        11. Special leave benefits for women* – up to 2 months
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         The application may be filed in advance, that is, at least five (5) days
                        prior to the scheduled date of the gynecological surgery that will be
                        undergone by the employee. In case of emergency, the application
                        for special leave shall be filed immediately upon employee’s return
                        but during confinement the agency shall be notified of said surgery.
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                         The application shall be accompanied by a medical certificate filled
                        out by the proper medical authorities, e.g. the attending surgeon
                        accompanied by a clinical summary reflecting the gynecological
                        disorder which shall be addressed or was addressed by the said
                        surgery; the histopathological report; the operative technique used
                        for the surgery; the duration of the surgery including the perioperative period
                        (period of confinement around surgery); as well as
                        the employees estimated period of recuperation for the same.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        12. Special Emergency (Calamity) leave – up to 5 days
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         The special emergency leave can be applied for a maximum of five
                        (5) straight working days or staggered basis within thirty (30) days
                        from the actual occurrence of the natural calamity/disaster. Said
                        privilege shall be enjoyed once a year, not in every instance of
                        calamity or disaster
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                         The head of office shall take full responsibility for the grant of special
                        emergency leave and verification of the employee’s eligibility to be
                        granted thereof. Said verification shall include: validation of place of
                        residence based on latest available records of the affected
                        employee; verification that the place of residence is covered in the
                        declaration of calamity area by the proper government agency; and
                        such other proofs as may be necessary.
                    </p>

                    <p style="font-weight: 600; margin-bottom: 3px">
                        13. Monetization of leave credits</p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        Application for monetization of fifty percent (50%) or more of the
                        accumulated leave credits shall be accompanied by letter request to
                        the head of the agency stating the valid and justifiable reasons.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        7. Solo Parent leave – 7 days
                    </p>
                    <p style="margin-bottom: 10px; margin-left: .8rem;">
                        It shall be filed in advance or whenever possible five (5) days before
                        going on such leave with updated Solo Parent Identification Card.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        14. Terminal leave*
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                        Proof of employee’s resignation or retirement or separation from the
                        service.
                    </p>
                    <p style="font-weight: 600; margin-bottom: 3px">
                        15. Adoption Leave
                    </p>
                    <p style="margin-bottom: 5px; margin-left: .8rem;">
                         Application for adoption leave shall be filed with an authenticated
                        copy of the Pre-Adoptive Placement Authority issued by the
                        Department of Social Welfare and Development (DSWD).
                    </p>
                </div>

            </div>
            <div>
                <p style="text-align: justify; margin: 0; font-size: 10px">
                    *For leave of absence for thirty (30) calendar days or more and terminal leave, application
                    shall be accompanied by a clearance from money, property and
                    work-related accountabilities (pursuant to CSC Memorandum Circular No. 2, s. 1985). </p>
            </div>
        </div>
        <style>
        .page-two p {
            text-align: justify;
            margin: 0;
        }
        </style>

    </div>

    <script>
    window.onafterprint = () => window.close();
    </script>

</body>

</html>