<?php
$conn = new mysqli('localhost', 'u164243783_ticket', '^^_ICTHOSTING2025_mysqluserpassword', 'u164243783_ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';

$sql = "SELECT id, requesting_official_name, date_requested_assistance, time_requested_assistance, date_filed 
        FROM helpdesk 
        WHERE status = ? 
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>
