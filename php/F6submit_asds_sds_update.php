<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "u155592346_usr_icthub", "+kuydZ4M", "u155592346_db_icthub");
// Collect POST data
$parent_id = $_POST['parent_id'] ?? null;
$asds_sds_esign = $_POST['asds_sds_esign'] ?? null;
$approve_for_days_with_pay = (int) ($_POST['approve_for_days_with_pay'] ?? 0);
$approve_for_days_without_pay = (int) ($_POST['approve_for_days_without_pay'] ?? 0);
$approve_for_others = $_POST['approve_for_others'] ?? '';
$disapproved_due_to = $_POST['disapproved_due_to'] ?? '';

file_put_contents("debug.txt", print_r($_POST, true));

if (!$parent_id) {
  echo json_encode(['success' => false, 'error' => 'Missing parent_id']);
  exit;
}

$stmt = $conn->prepare("
  UPDATE form6_applicationforleave_other_updates
  SET
    asds_sds_esign = ?,
    approve_for_days_with_pay = ?,
    approve_for_days_without_pay = ?,
    approve_for_others = ?,
    disapproved_due_to = ?
  WHERE parent_id = ?
");

$stmt->bind_param(
  "siissi",
  $asds_sds_esign,
  $approve_for_days_with_pay,
  $approve_for_days_without_pay,
  $approve_for_others,
  $disapproved_due_to,
  $parent_id
);

if ($stmt->execute() && $stmt->affected_rows > 0) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'No matching parent_id found or no changes made']);
}
?>