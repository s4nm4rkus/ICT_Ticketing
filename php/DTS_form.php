<?php
$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT dtsNumber, date, requesterName, mobileNumber, school, requestType,
                 unitName, reason, newTitle, editReason, cancelReason,
                 emailAddress, EmailAddress, documentType, processDays, newEmail, status
           FROM dtsrequest WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        if (!isset($data['status']) || $data['status'] == '') {
            $data['status'] = 'Pending';
        }
        
        echo json_encode(["success" => true, "ticket" => $data]);
    } else {
        echo json_encode(["success" => false, "message" => "No record found"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

$conn->close();
?>