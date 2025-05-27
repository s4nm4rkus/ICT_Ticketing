<?php
session_start();

$allowedUserIDs = [1];
if (
    !isset($_SESSION['loggedIn']) ||
    $_SESSION['loggedIn'] !== true ||
    !isset($_SESSION['userID']) ||
    !in_array($_SESSION['userID'], $allowedUserIDs)
) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/list.css" />
    <title>DTS Request List</title>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <a href="admin.php">
                    <img src="./Images/logo.jpg" alt="Lorem Ipsum Logo">
                </a>
                <h1>SDO Tayabas</h1>
            </div>
            <button class="list-btn" onclick="location.href='admin.php'">Back</button>
        </header>
    </div>
    
    <div class="list-container">
        <div class="banner ict-banner">
            <div class="banner-overlay"></div>
            <a href="ict_list.php" class="arrow-button arrow-left">
                <img src="./Images/left-chevron.png" alt="Left Arrow">
            </a>
            <span class="title">DTS Request List</span>
            <a href="email_list.php" class="arrow-button arrow-right">
                <img src="./Images/right-chevron.png" alt="Right Arrow">
            </a>
        </div>
        <div class="section">
            <div class="section-title">Pending</div>
            <div class="section-content">
                <div class="box-header">Pending Ticket Request</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Request Type</th>
                            <th>Date Reported</th>
                        </tr>
                    </thead>
                    <tbody class="recent-tickets">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Approved</div>
            <div class="section-content">
                <div class="box-header">Approved Ticket Request</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Request Type</th>
                            <th>Date Reported</th>
                        </tr>
                    </thead>
                    <tbody class="history-tickets">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="gov-logos">
        <div class="logo-container">
            <img src="./Images/transparency.png" alt="Transparency Seal">
        </div>
        
        <div class="logo-container">
            <img src="./Images/deped.png" alt="DepEd Matatag Logo">
        </div>
        
        <div class="logo-container">
            <img src="./Images/pilipinas.png" alt="Bagong Pilipinas Logo">
        </div>
        
        <div class="logo-container">
            <img src="./Images/freedom.png" alt="Freedom of Information Logo">
        </div>
    </div>
    
    <footer class="footer">
        <p>Copyright © 2025 Ticketing. All Rights Reserved</p>
        <div class="developer-credits">
            <p>Developed by: Angela Faith M. Salazar and Arien R. Peredo</p>
        </div>
    </footer>
    <script src="js/DTS_list.js"></script>
</body>
</html>