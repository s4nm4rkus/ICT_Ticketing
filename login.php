<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT userid, username FROM AdminLogin WHERE BINARY username = ? AND BINARY password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $_SESSION['username'] = $user['username']; 
    $_SESSION['userID'] = $user['userid'];   
    $_SESSION['loggedIn'] = true; 

    header("Location: admin.php"); 
    exit();
} else {
    echo "<script>alert('Invalid username or password. Please try again.');
    window.location.href='login.html';</script>";
    exit(); 
}

$stmt->close();
$conn->close();
?>