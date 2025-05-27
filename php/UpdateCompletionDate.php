<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => "Connection failed: " . $conn->connect_error]));
}

// Get request data from POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['requestType']) || !isset($data['requestId']) || !isset($data['completedDate'])) {
    die(json_encode(['success' => false, 'error' => 'Missing required data']));
}

$requestType = $data['requestType'];
$requestId = $data['requestId'];
$completedDate = $data['completedDate'] ? $data['completedDate'] : null;

// Update the appropriate table based on requestType
$success = false;
$error = '';

try {
    switch ($requestType) {
        case 'ICT Technical Assistance':
            $table = 'icttechnicalassistance';
            break;
        case 'DTS Request':
            $table = 'dtsrequest';
            break;
        case 'Email Request':
            $table = 'emailrequest';
            break;
        case 'Help Desk':
            $table = 'helpdesk';
            break;
        default:
            throw new Exception('Unknown request type');
    }
    
    // Prepare SQL based on whether date is null or not
    if ($completedDate) {
        $sql = "UPDATE $table SET date_completed = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $completedDate, $requestId);
    } else {
        $sql = "UPDATE $table SET date_completed = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $requestId);
    }
    
    $success = $stmt->execute();
    
    if (!$success) {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Return result as JSON
header('Content-Type: application/json');
echo json_encode(['success' => $success, 'error' => $error]);

// Close the connection
$conn->close();
?>