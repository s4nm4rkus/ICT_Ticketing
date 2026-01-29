<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "ticket");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => $conn->connect_error]);
    exit;
}

$parent_id = $_GET['parent_id'] ?? '';

if (!$parent_id) {
    echo json_encode(["success" => false, "error" => "Missing parent ID"]);
    exit;
}

// Fetch latest child data for this parent
$sql = "SELECT * FROM form6_applicationforleave_personnel_updates
        WHERE parent_id = ?
        ORDER BY submitted_at DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["success" => true, "data" => $row]);
} else {
    // No data yet, return empty
    echo json_encode([
        "success" => true,
        "data" => [
            "as_of" => "",
            "vacation_leave_total_earned" => "",
            "sick_leave_total_earned" => "",
            "vacation_leave_less_this_application" => "",
            "sick_leave_less_this_application" => "",
            "vacation_leave_balance" => "",
            "sick_leave_balance" => ""
        ]
    ]);
}

$stmt->close();
$conn->close();
?>