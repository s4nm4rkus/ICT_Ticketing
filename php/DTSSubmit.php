<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ticket"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date = $_POST['date'];
$dtsNumber = $_POST['dtsNumber'];
$requesterName = $_POST['requesterName'];
$mobileNumber = $_POST['mobileNumber'];
$school = $_POST['school'];
$requestType = $_POST['requestType'];

$unitName = isset($_POST['unitName']) ? $_POST['unitName'] : NULL;
$reason = isset($_POST['reason']) ? $_POST['reason'] : NULL;
$newTitle = isset($_POST['newTitle']) ? $_POST['newTitle'] : NULL;
$editReason = isset($_POST['editReason']) ? $_POST['editReason'] : NULL;
$cancelReason = isset($_POST['cancelReason']) ? $_POST['cancelReason'] : NULL;
$emailAddress = isset($_POST['emailAddress']) ? $_POST['emailAddress'] : NULL;
$documentType = isset($_POST['documentType']) ? $_POST['documentType'] : NULL;
$processDays = isset($_POST['processDays']) ? $_POST['processDays'] : NULL;
$newEmail = isset($_POST['newEmail']) ? $_POST['newEmail'] : NULL;

$sql = "INSERT INTO DTSRequest (date, dtsNumber, requesterName, mobileNumber, school, requestType, unitName, reason, newTitle, editReason, cancelReason, emailAddress, documentType, processDays, newEmail, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssis", $date, $dtsNumber, $requesterName, $mobileNumber, $school, $requestType, $unitName, $reason, $newTitle, $editReason, $cancelReason, $emailAddress, $documentType, $processDays, $newEmail);

if ($stmt->execute()) {
    echo "<script>alert('Form submitted successfully!'); window.location.href='technical_assistance.html';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
