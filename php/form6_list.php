<?php
session_start(); // Start the session

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    echo json_encode([]);
    exit;
}

// Get the current user's role and department for unit-head
$role = $_SESSION['role'] ?? '';
$userDepartment = $_SESSION['department'] ?? '';

// Connect to DB
$conn = new mysqli("localhost", "u155592346_usr_icthub", "+kuydZ4M", "u155592346_db_icthub");if ($conn->connect_error) {
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
    'unit-head'    => ['For Recommendation', 'Approved', 'Disapproved'],
    'records'    => ['For Records Unit', 'Approved', 'Disapproved'],
    'personnel'  => ['For Personnel Unit', 'Approved', 'Disapproved'],
    'admin'      => ['For Admin Unit', 'Approved', 'Disapproved'],
    'asds-sds'   => ['For SDS/ASDS/Records', 'Approved', 'Disapproved'],
    'ict'        => ['For Recommendation','For Records Unit', 'For Personnel Unit', 'For Admin Unit', 'For SDS/ASDS/Records', 'Approved', 'Disapproved'],
];

// Get allowed statuses for this role
$allowedStatuses = $roleAccess[$role] ?? [];

// Only keep statuses requested by JS that are allowed for this role
$statuses = array_intersect($requestedStatuses, $allowedStatuses);

if (!$statuses) {
    echo json_encode([]); // Nothing allowed for this role
    exit;
}

// Prepare placeholders
$placeholders = implode(",", array_fill(0, count($statuses), "?"));

if ($role === 'unit-head') {

    $userDepartment = $_SESSION['department'] ?? '';

    $sql = "SELECT * FROM form6_applicationforleave 
            WHERE (
                status IN ($placeholders)
                AND (
                    status != 'For Recommendation'
                    OR department = ?
                )
            )
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);

    $typeStr = str_repeat("s", count($statuses)) . "s";
    $params = array_merge($statuses, [$userDepartment]);

    $stmt->bind_param($typeStr, ...$params);

} else {

    $sql = "SELECT * FROM form6_applicationforleave 
            WHERE status IN ($placeholders) 
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);

    $typeStr = str_repeat("s", count($statuses));
    $stmt->bind_param($typeStr, ...$statuses);
}


$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

// Return JSON
echo json_encode($tickets);

$stmt->close();
$conn->close();
?>