<?php
header('Content-Type: application/json');

// DEBUG (set to 0 in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB CONNECTION
$conn = new mysqli("localhost", "root", "", "ticket");

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// SAFE ARRAY HANDLING
function getArray($key) {
    return isset($_POST[$key]) && is_array($_POST[$key]) ? $_POST[$key] : [];
}

// --- MAIN FIELDS ---
$department     = $_POST['department'] ?? '';
$last_name      = $_POST['last_name'] ?? '';
$first_name     = $_POST['first_name'] ?? '';
$middle_name    = $_POST['middle_name'] ?? '';
$email          = $_POST['email'] ?? '';
$date_of_filing = $_POST['date_of_filing'] ?? '';
$position       = $_POST['position'] ?? '';
$salary         = $_POST['salary'] ?? '';

// --- TYPE OF LEAVE ---
$selectedOptions = getArray('selectedOptions');
$typeofleave_A = [];
$other_type_of_leave = '';

foreach ($selectedOptions as $option) {
    if ($option === 'Others') {
        $other_type_of_leave = $_POST['others_text'] ?? '';
        $typeofleave_A[] = 'Others';
    } else {
        $typeofleave_A[] = $option;
    }
}
$typeofleave_A = implode(", ", $typeofleave_A);

// --- SPECIFICATION ---
$specifications = [];

if (!empty($_POST['within_ph_text'])) $specifications[] = "Within Philippines: " . $_POST['within_ph_text'];
if (!empty($_POST['abroad_text'])) $specifications[] = "Abroad: " . $_POST['abroad_text'];
if (!empty($_POST['in_hospital_text'])) $specifications[] = "In Hospital: " . $_POST['in_hospital_text'];
if (!empty($_POST['out_patient_text'])) $specifications[] = "Out Patient: " . $_POST['out_patient_text'];
if (!empty($_POST['special_leave_BW_spec'])) $specifications[] = "Special Leave (Women): " . $_POST['special_leave_BW_spec'];
if (isset($_POST['completion_of_masters_degree'])) $specifications[] = "Completion of Master's Degree";
if (isset($_POST['BAR_Board_exam'])) $specifications[] = "BAR/Board Examination Review";

$specification_of_leave = implode("; ", $specifications);

// --- OTHER FIELDS ---
$other_purpose = implode(", ", getArray('otherPurpose'));
$communication = implode(", ", getArray('communication'));

$number_of_days_applied = $_POST['number_of_days_applied'] ?? '';
$inclusive_days = $_POST['inclusive_days'] ?? '';

$name_of_official = $_POST['name_of_official'] ?? '';
$signatory_position = $_POST['signatory_position'] ?? '';

$status = "For Recommendation";

// --- CHECK AUTHORIZER ---
$stmt = $conn->prepare("SELECT officer_name FROM department_authorizers");

if ($stmt) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        $applicant_fullname = strtolower(trim($first_name . ' ' . $last_name));

        while ($row = $result->fetch_assoc()) {
            $officer_fullname = strtolower(trim($row['officer_name']));

            if ($applicant_fullname === $officer_fullname) {
                $status = "For Records Unit";
                break;
            }
        }
    }
    $stmt->close();
}

// --- FILE UPLOAD ---
$e_signature = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {

    $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if ($fileExt !== 'png') {
        echo json_encode(["status" => "error", "message" => "Only PNG allowed"]);
        exit;
    }

    if ($_FILES['file']['size'] > 25000000) {
        echo json_encode(["status" => "error", "message" => "File too large"]);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/e_signatures/applicants/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = 'sign_' . uniqid() . '.png';
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $e_signature = 'uploads/e_signatures/applicants/' . $fileName;
    } else {
        echo json_encode(["status" => "error", "message" => "Upload failed"]);
        exit;
    }
}

// --- INSERT ---
$sql = "INSERT INTO form6_applicationforleave 
(department, last_name, first_name, middle_name, salary, position, email, date_of_filing, typeofleave_A, other_type_of_leave, 
specification_of_leave, other_purpose, number_of_days_applied, inclusive_days, communication, name_of_official, 
signatory_position, e_signature, status, submitted_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "sssssssssssssssssss",
    $department,
    $last_name,
    $first_name,
    $middle_name,
    $salary,
    $position,
    $email,
    $date_of_filing,
    $typeofleave_A,
    $other_type_of_leave,
    $specification_of_leave,
    $other_purpose,
    $number_of_days_applied,
    $inclusive_days,
    $communication,
    $name_of_official,
    $signatory_position,
    $e_signature,
    $status
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>