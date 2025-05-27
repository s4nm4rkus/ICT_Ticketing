<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ticket"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date_filed = $_POST['date'];
$request_type = $_POST['request_type'];
$full_name = $_POST['name'];
$personal_email = $_POST['email'];
$cellphone_number = $_POST['phone'];
$school_name = $_POST['school'];

$appointment_letter = "";
if (!empty($_FILES['appointmentLetter']['name'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["appointmentLetter"]["name"]);
    
    if (move_uploaded_file($_FILES["appointmentLetter"]["tmp_name"], $target_file)) {
        $appointment_letter = $target_file;
    } else {
        echo "error_file_upload";
        exit();
    }
}

$sql = "INSERT INTO EmailRequest (date_filed, request_type, full_name, personal_email, cellphone_number, school_name, appointment_letter) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $date_filed, $request_type, $full_name, $personal_email, $cellphone_number, $school_name, $appointment_letter);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
