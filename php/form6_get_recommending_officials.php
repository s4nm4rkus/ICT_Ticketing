<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "ticket");
if ($conn->connect_error) {
    echo json_encode(null);
    exit;
}

$leave_id = $_GET['id'] ?? null;
if (!$leave_id) {
    echo json_encode(null);
    exit;
}

/* Get applicant info */
$stmt = $conn->prepare("SELECT department, first_name, last_name FROM form6_applicationforleave WHERE id = ?");
$stmt->bind_param("i", $leave_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();

$department = $app['department'] ?? null;
if (!$department) {
    echo json_encode(null);
    exit;
}

/* Get department authorizer */
$stmt2 = $conn->prepare("
    SELECT officer_name, officer_position
    FROM department_authorizers
    WHERE department = ?
");
$stmt2->bind_param("s", $department);
$stmt2->execute();
$auth = $stmt2->get_result()->fetch_assoc();

if (!$auth) {
    echo json_encode(null);
    exit;
}

/* Normalize names */
$applicant_fullname = strtolower(trim(preg_replace('/\s+/', ' ', $app['first_name'] . ' ' . $app['last_name'])));
$officer_fullname   = strtolower(trim(preg_replace('/\s+/', ' ', $auth['officer_name'])));

/* If same person → no recommending officer */
if ($applicant_fullname === $officer_fullname) {
    echo json_encode(null);
    exit;
}

/* Otherwise return officer */
echo json_encode($auth);