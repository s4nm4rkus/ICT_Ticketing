<?php
$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM icttechnicalassistance WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        if (!isset($data['status']) || $data['status'] == '') {
            $data['status'] = 'Pending';
        }
        
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'No record found']);
    }

    $stmt->close();
}

$conn->close();
?>