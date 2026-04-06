<?php
session_start();


$conn = new mysqli('localhost', 'u155592346_usr_icthub', '+kuydZ4M', 'u155592346_db_icthub');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

// Fetch user including the role
$stmt = $conn->prepare("SELECT userid, username, role, department FROM adminlogin WHERE BINARY username = ? AND BINARY password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Store role in session
    $_SESSION['username'] = $user['username']; 
    $_SESSION['userID']   = $user['userid'];   
    $_SESSION['role']     = $user['role'];
    $_SESSION['department'] = $user['department'];     // <-- important!
    $_SESSION['loggedIn'] = true; 

    // var_dump($_SESSION);
    // exit;

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