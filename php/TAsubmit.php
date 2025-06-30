<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$others_text = (in_array("Others", $selectedOptions) && !empty($_POST['others_text'])) ? $_POST['others_text'] : "";
$assistance = implode(", ", $selectedOptions);
$description = $_POST['description'];

$sql = "INSERT INTO icttechnicalassistance (requester_name, position, date_reported, department, assistance, other_assistance, description, submitted_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $name, $position, $date_reported, $department, $assistance, $others_text, $description);

echo $stmt->execute() ? "success" : "error";

$stmt->close();
$conn->close();
?>
