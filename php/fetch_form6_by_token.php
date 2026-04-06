<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "u155592346_usr_icthub", "+kuydZ4M", "u155592346_db_icthub");
$token = $_GET['token'] ?? '';

$stmt = $conn->prepare("
    SELECT *
    FROM form6_applicationforleave
    WHERE print_token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false]);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => $result->fetch_assoc()
]);