<?php
$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM emailrequest WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'No record found']);
    }

    $stmt->close();
}

if (isset($_POST['action']) && $_POST['action'] == 'approve' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $sql = "UPDATE emailrequest SET status = 'Approved' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to update status']);
    }
    
    $stmt->close();
}

$conn->close();
?>