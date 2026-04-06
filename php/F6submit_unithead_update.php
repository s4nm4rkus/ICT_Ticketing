<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$conn = new mysqli("localhost", "u155592346_usr_icthub", "+kuydZ4M", "u155592346_db_icthub");
$parent_id = $_POST['parent_id'] ?? null;
$unit_head_esign = $_POST['unit_head_esign'] ?? '';
$unit_head_recommendation = $_POST['unit_head_recommendation'] ?? '';

if (!$parent_id) {
    echo json_encode(['success' => false, 'error' => 'Missing parent_id']);
    exit;
}

// Check if record exists, insert if not
$checkStmt = $conn->prepare("SELECT id FROM form6_applicationforleave_other_updates WHERE parent_id = ?");
$checkStmt->bind_param("i", $parent_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO form6_applicationforleave_other_updates (parent_id) VALUES (?)");
    $insert->bind_param("i", $parent_id);
    $insert->execute();
    $insert->close();
}
$checkStmt->close();

// Update only unit_head fields
$stmt = $conn->prepare("
    UPDATE form6_applicationforleave_other_updates
    SET unit_head_esign = ?,
        unit_head_recommendation = ?
    WHERE parent_id = ?
");
$stmt->bind_param("ssi", $unit_head_esign, $unit_head_recommendation, $parent_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>