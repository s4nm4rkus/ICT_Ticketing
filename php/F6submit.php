<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ticket";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Collect main fields ---
$department     = $_POST['department'] ?? '';
$last_name      = $_POST['last_name'] ?? '';
$first_name     = $_POST['first_name'] ?? '';
$middle_name    = $_POST['middle_name'] ?? '';
$date_of_filing = $_POST['date_of_filing'] ?? '';
$position       = $_POST['position'] ?? '';
$salary         = $_POST['salary'] ?? '';

// --- Collect checkboxes (Part A) ---
$selectedOptions = isset($_POST['selectedOptions']) ? $_POST['selectedOptions'] : [];
$typeofleave_A   = implode(", ", $selectedOptions);

// --- Build Specification of Leave (Part B) ---
$specifications = [];

// Vacation / Special Privilege
if (!empty($_POST['within_ph_text'])) {
    $specifications[] = "Within Philippines: " . $_POST['within_ph_text'];
}
if (!empty($_POST['abroad_text'])) {
    $specifications[] = "Abroad: " . $_POST['abroad_text'];
}

// Sick Leave
if (!empty($_POST['in_hospital_text'])) {
    $specifications[] = "In Hospital: " . $_POST['in_hospital_text'];
}
if (!empty($_POST['out_patient_text'])) {
    $specifications[] = "Out Patient: " . $_POST['out_patient_text'];
}

// Special Leave for Women
if (!empty($_POST['special_leave_BW_spec'])) {
    $specifications[] = "Special Leave (Women): " . $_POST['special_leave_BW_spec'];
}

// Study Leave (checkbox only)
if (isset($_POST['completion_of_masters_degree'])) {
    $specifications[] = "Completion of Master's Degree";
}
if (isset($_POST['BAR_Board_exam'])) {
    $specifications[] = "BAR/Board Examination Review";
}

// Others
if (!empty($_POST['others_text'])) {
    $specifications[] = "Others: " . $_POST['others_text'];
}

// Final joined specification string
$specification_of_leave = implode("; ", $specifications);

$number_of_days_applied = $_POST['number_of_days_applied'] ?? '';
$inclusive_days = $_POST['inclusive_days'] ?? '';

// --- Communication (checkboxes) ---
$communicationOptions = isset($_POST['communication']) ? $_POST['communication'] : [];
$communication = implode(", ", $communicationOptions);

$name_of_official = $_POST['name_of_official'] ?? '';
$signatory_position = $_POST['signatory_position']?? '';


// --- Insert into DB ---
$sql = "INSERT INTO form6_applicationforleave 
    (department, last_name, first_name, middle_name, salary, position, date_of_filing, typeofleave_A, specification_of_leave, 
    number_of_days_applied, inclusive_days, communication, name_of_official, signatory_position, submitted_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssss",
    $department,
    $last_name,
    $first_name,
    $middle_name,
    $salary,
    $position,
    $date_of_filing,
    $typeofleave_A,
    $specification_of_leave,
    $number_of_days_applied, 
    $inclusive_days, 
    $communication, 
    $name_of_official, 
    $signatory_position
);

echo $stmt->execute() ? "success" : "error";

$stmt->close();
$conn->close();
?>
