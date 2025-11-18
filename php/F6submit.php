<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // ensure JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ticket";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// --- Collect main fields ---
$department     = $_POST['department'] ?? '';
$last_name      = $_POST['last_name'] ?? '';
$first_name     = $_POST['first_name'] ?? '';
$middle_name    = $_POST['middle_name'] ?? '';
$email          = $_POST['email'] ?? '';
$date_of_filing = $_POST['date_of_filing'] ?? '';
$position       = $_POST['position'] ?? '';
$salary         = $_POST['salary'] ?? '';

// --- Collect checkboxes (Part A) ---
$selectedOptions = $_POST['selectedOptions'] ?? [];
$typeofleave_A   = [];
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

// --- Build Specification of Leave ---
$specifications = [];

if (!empty($_POST['within_ph_text'])) $specifications[] = "Within Philippines: " . $_POST['within_ph_text'];
if (!empty($_POST['abroad_text'])) $specifications[] = "Abroad: " . $_POST['abroad_text'];
if (!empty($_POST['in_hospital_text'])) $specifications[] = "In Hospital: " . $_POST['in_hospital_text'];
if (!empty($_POST['out_patient_text'])) $specifications[] = "Out Patient: " . $_POST['out_patient_text'];
if (!empty($_POST['special_leave_BW_spec'])) $specifications[] = "Special Leave (Women): " . $_POST['special_leave_BW_spec'];
if (isset($_POST['completion_of_masters_degree'])) $specifications[] = "Completion of Master's Degree";
if (isset($_POST['BAR_Board_exam'])) $specifications[] = "BAR/Board Examination Review";

$specification_of_leave = implode("; ", $specifications);

// --- Other Fields ---
$otherPurposeOptions = $_POST['otherPurpose'] ?? [];
$other_purpose = implode(", ", $otherPurposeOptions);

$number_of_days_applied = $_POST['number_of_days_applied'] ?? '';
$inclusive_days = $_POST['inclusive_days'] ?? '';

$communicationOptions = $_POST['communication'] ?? [];
$communication = implode(", ", $communicationOptions);

$name_of_official = $_POST['name_of_official'] ?? '';
$signatory_position = $_POST['signatory_position'] ?? '';

// --- Handle E-Signature Upload ---
$e_signature = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['png'];

    if (in_array($fileExt, $allowed)) {
        if ($fileSize < 25000000) {
            $uploadDir = __DIR__ . '/uploads/e_signatures/applicants/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // ensure directory exists
            }

            $fileNameNew = 'sign_' . uniqid('', true) . "." . $fileExt;
            $fileDestination = $uploadDir . $fileNameNew;

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                $e_signature = 'uploads/e_signatures/applicants/' . $fileNameNew; // relative path stored in DB
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "File too large."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid file type. Only PNG allowed."]);
        exit;
    }
}

// --- Insert into DB ---
$sql = "INSERT INTO form6_applicationforleave 
    (department, last_name, first_name, middle_name, salary, position, email, date_of_filing, typeofleave_A, other_type_of_leave, 
     specification_of_leave, other_purpose, number_of_days_applied, inclusive_days, communication, name_of_official, 
     signatory_position, e_signature, submitted_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssssssss",
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
    $e_signature
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
