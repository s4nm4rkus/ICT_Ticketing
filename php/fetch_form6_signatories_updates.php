<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "ticket");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get parent_id from GET
$parent_id = $_GET['parent_id'] ?? '';
if (!$parent_id) {
    echo json_encode(['success' => false, 'error' => 'Missing parent ID']);
    exit;
}

// Fetch latest record for this parent_id
$sql = "
SELECT 
    p.status AS parent_status,
    c.*
FROM form6_applicationforleave p
LEFT JOIN form6_applicationforleave_other_updates c
    ON p.id = c.parent_id
WHERE p.id = ?
LIMIT 1
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if row exists
if ($row = $result->fetch_assoc()) {
    // Ensure all fields exist
    $row = array_merge([
    'admin_esign' => '',
    'asds_sds_esign' => '',
    'unit_head_esign' => '',
    'unit_head_recommendation' => '',
    'approve_for_days_with_pay' => '',
    'approve_for_days_without_pay' => '',
    'approve_for_others' => '',
    'disapproved_due_to' => '',
    'parent_status' => ''
], $row);


    echo json_encode(['success' => true, 'data' => $row]);
} else {
    // No record found, return empty defaults
    echo json_encode(['success' => true, 'data' => [
    'admin_esign' => '',
    'asds_sds_esign' => '',
    'unit_head_esign' => '',
    'unit_head_recommendation' => '',
    'approve_for_days_with_pay' => '',
    'approve_for_days_without_pay' => '',
    'approve_for_others' => '',
    'disapproved_due_to' => '',
    'parent_status' => ''
]]);

}

$stmt->close();
$conn->close();
?>