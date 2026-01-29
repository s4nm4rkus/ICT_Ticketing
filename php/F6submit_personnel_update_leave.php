<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "ticket");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => $conn->connect_error]);
    exit;
}

$parent_id = $_POST['parent_id'] ?? '';
$as_of = $_POST['as_of'] ?? '';
$vacation_total = $_POST['vacation_leave_total_earned'] ?? '';
$sick_total = $_POST['sick_leave_total_earned'] ?? '';
$vacation_less = $_POST['vacation_leave_less_this_application'] ?? '';
$sick_less = $_POST['sick_leave_less_this_application'] ?? '';
$vacation_balance = $_POST['vacation_leave_balance'] ?? '';
$sick_balance = $_POST['sick_leave_balance'] ?? '';

if (!$parent_id) {
    echo json_encode(["success" => false, "error" => "Missing parent ID"]);
    exit;
}

// Check if child record exists
$check = $conn->prepare("SELECT id FROM form6_applicationforleave_personnel_updates WHERE parent_id = ? LIMIT 1");
$check->bind_param("i", $parent_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Child exists → UPDATE
    $update = $conn->prepare("UPDATE form6_applicationforleave_personnel_updates 
        SET as_of=?, vacation_leave_total_earned=?, sick_leave_total_earned=?,
            vacation_leave_less_this_application=?, sick_leave_less_this_application=?,
            vacation_leave_balance=?, sick_leave_balance=?
        WHERE parent_id=?");
    $update->bind_param(
        "sssssssi",
        $as_of,
        $vacation_total,
        $sick_total,
        $vacation_less,
        $sick_less,
        $vacation_balance,
        $sick_balance,
        $parent_id
    );
    $success = $update->execute();
    $update->close();
} else {
    // Child does not exist → INSERT
    $insert = $conn->prepare("INSERT INTO form6_applicationforleave_personnel_updates 
        (parent_id, as_of, vacation_leave_total_earned, sick_leave_total_earned,
        vacation_leave_less_this_application, sick_leave_less_this_application,
        vacation_leave_balance, sick_leave_balance)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param(
        "isssssss",
        $parent_id,
        $as_of,
        $vacation_total,
        $sick_total,
        $vacation_less,
        $sick_less,
        $vacation_balance,
        $sick_balance
    );
    $success = $insert->execute();
    $insert->close();
}

$check->close();
$conn->close();

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
?>