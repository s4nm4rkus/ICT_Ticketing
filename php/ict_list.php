<?php
$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';

$sql = "SELECT id, requester_name, assistance, date_reported 
        FROM icttechnicalassistance 
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
