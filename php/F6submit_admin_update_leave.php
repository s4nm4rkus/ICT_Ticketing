<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "u155592346_usr_icthub", "+kuydZ4M", "u155592346_db_icthub");
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => $conn->connect_error
    ]);
    exit;
}

// Get POST data
$parent_id  = $_POST['parent_id'] ?? '';
$admin_esign = $_POST['admin_esign'] ?? '';

if (!$parent_id) {
    echo json_encode([
        "success" => false,
        "error" => "Missing parent ID"
    ]);
    exit;
}

// Check if child record exists
$check = $conn->prepare("
    SELECT id 
    FROM form6_applicationforleave_other_updates 
    WHERE parent_id = ? 
    LIMIT 1
");
$check->bind_param("i", $parent_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // UPDATE existing record
    $update = $conn->prepare("
        UPDATE form6_applicationforleave_other_updates 
        SET admin_esign = ? 
        WHERE parent_id = ?
    ");
    $update->bind_param("si", $admin_esign, $parent_id);
    $success = $update->execute();
    $update->close();
} else {
    // INSERT new record
    $insert = $conn->prepare("
        INSERT INTO form6_applicationforleave_other_updates (parent_id, admin_esign) 
        VALUES (?, ?)
    ");
    $insert->bind_param("is", $parent_id, $admin_esign);
    $success = $insert->execute();
    $insert->close();
}

// Close connections
$check->close();
$conn->close();

// Return JSON response
echo json_encode(["success" => $success]);
?>