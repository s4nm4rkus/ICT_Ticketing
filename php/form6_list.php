<?php
session_start(); // Start the session

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    echo json_encode([]);
    exit;
}

// Get the current user's role
$role = $_SESSION['role'] ?? '';

// Connect to DB
$conn = new mysqli("localhost", "root", "", "ticket");
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

// Get status from query string
$statusParam = $_GET['status'] ?? '';
if (!$statusParam) {
    echo json_encode([]);
    exit;
}

// Convert to array
$requestedStatuses = explode(",", $statusParam);

// Map roles to accessible statuses
$roleAccess = [
    'records'    => ['For Records Unit', 'Approved', 'Disapproved'],
    'personnel'  => ['For Personnel Unit', 'Approved', 'Disapproved'],
    'admin'      => ['For Admin Unit', 'Approved', 'Disapproved'],
    'asds-sds'   => ['For SDS/ASDS/Records', 'Approved', 'Disapproved'],
    'ict'        => ['For Records Unit', 'For Personnel Unit', 'For Admin Unit', 'For SDS/ASDS/Records', 'Approved', 'Disapproved'],
];

// Get allowed statuses for this role
$allowedStatuses = $roleAccess[$role] ?? [];

// Only keep statuses requested by JS that are allowed for this role
$statuses = array_intersect($requestedStatuses, $allowedStatuses);

if (!$statuses) {
    echo json_encode([]); // Nothing allowed for this role
    exit;
}

// Prepare placeholders for prepared statement
$placeholders = implode(",", array_fill(0, count($statuses), "?"));
$sql = "SELECT * FROM form6_applicationforleave WHERE status IN ($placeholders) ORDER BY id DESC";
$stmt = $conn->prepare($sql);

// Bind params dynamically
$typeStr = str_repeat("s", count($statuses));
$stmt->bind_param($typeStr, ...$statuses);

$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

// Return JSON
echo json_encode($tickets);

$stmt->close();
$conn->close();
?>