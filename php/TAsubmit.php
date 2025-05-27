<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "ticket"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$position = $_POST['position'];
$date_reported = $_POST['date'];
$department = $_POST['department'];

$selectedOptions = isset($_POST['selectedOptions']) ? $_POST['selectedOptions'] : [];

$others_text = "";
if (in_array("Others", $selectedOptions) && !empty($_POST['others_text'])) {
    $others_text = $_POST['others_text'];
}

$assistance = implode(", ", $selectedOptions);

$description = $_POST['description'];

$sql = "INSERT INTO icttechnicalassistance (requester_name, position, date_reported, department, assistance, other_assistance, description, submitted_at)
        VALUES ('$name', '$position', '$date_reported', '$department', '$assistance', '$others_text', '$description', NOW())";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Form submitted successfully!'); window.location.href='technical_assistance.html';</script>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
