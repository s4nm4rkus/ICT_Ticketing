<?php
$servername = "localhost";
$username = "u164243783_ticket"; 
$password = "^^_ICTHOSTING2025_mysqluserpassword"; 
$dbname = "u164243783_ticket"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date_filed = $_POST['date'];
$requesting_official_office = $_POST['office'];
$requesting_official_name = $_POST['name'];
$details_request = $_POST['details'];
$date_requested_assistance = $_POST['date1'];
$time_requested_assistance = $_POST['time'];
$specific_instructions = $_POST['description'];

$sql = "INSERT INTO helpdesk (date_filed, requesting_official_office, requesting_official_name, details_request, date_requested_assistance, time_requested_assistance, specific_instructions, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $date_filed, $requesting_official_office, $requesting_official_name, $details_request, $date_requested_assistance, $time_requested_assistance, $specific_instructions);

if ($stmt->execute()) {
    echo "success"; 
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
