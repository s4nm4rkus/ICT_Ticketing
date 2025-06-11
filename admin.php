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
    <link rel="stylesheet" type="text/css" href="css/admin.css" />
    <title>ICT Forms</title>
</head>

<div class="Top">
    <header>
        <div class="logo">
            <img src="./Images/logo.jpg" alt="Lorem Ipsum Logo">
            <h1>SDO Tayabas</h1>
        </div>
        <button class="admin-btn" onclick="showConfirmation()">Logout</button>

        <div class="overlay" id="overlay"></div>

        <div class="confirmation-dialog" id="confirmationDialog">
            <h3>Confirmation</h3>
            <p>Are you sure you want to logout?</p>
            <div class="dialog-buttons">
                <button class="confirm-btn" onclick="confirmLogout()">Yes</button>
                <button class="cancel-btn" onclick="hideConfirmation()">No</button>
            </div>
        </div>
    </header>


    <div class="edu-banner">
        <div class="banner-logos">
            <img src="./Images/depedlogo.png" alt="DepEd Logo" class="deped-logo" style="width: 140px; height: auto; margin-right: 20px;">
            <img src="./Images/newpilipinaslogo.png" alt="Philippines Logo" class="ph-logo"  style="width: 100px; height: auto; ">
            <img src="./Images/logo.png" alt="Tayabas City Logo" class="tayabas-logo">
        </div>
        <div class="banner-text">
            <h2>REPUBLIC OF THE PHILIPPINES</h2>
            <h1>DEPARTMENT OF EDUCATION</h1>
            <h3>SCHOOLS DIVISION OFFICE OF TAYABAS CITY</h3>
        </div>
        <div class="date-display">
            <p>Philippine Standard Time:</p>
            <p id="current-date-time"></p>
        </div>
    </div>
</div>

<body>

    <h1 class="admin-ict-title">ICT Tickect Analysis</h1>
    <button class="record-btn" onclick="location.href='record.php'">Generate Report</button>

        <div class="content-container">
            <div class="stats-container">
                <div class="stat-box" id="ict-stat" onclick="redirectTo('ict_list.php')">
                    <div class="stat-title">ICT TA Request Pending</div>
                    <div class="stat-number" id="ict-count">-</div>
                    <div>Submission</div>
                </div>
                <div class="stat-box" id="dts-stat" onclick="redirectTo('DTS_list.php')">
                    <div class="stat-title">DTS Request Pending</div>
                    <div class="stat-number" id="dts-count">-</div>
                    <div>Submission</div>
                </div>
                <div class="stat-box" id="email-stat" onclick="redirectTo('email_list.php')">
                    <div class="stat-title">Email Request Pending</div>
                    <div class="stat-number" id="email-count">-</div>
                    <div>Submission</div>
                </div>
                <div class="stat-box" id="help-stat" onclick="redirectTo('help_list.php')">
                    <div class="stat-title">Help Desk Request Pending</div>
                    <div class="stat-number" id="help-count">-</div>
                    <div>Submission</div>
                </div>
            </div>

            <script>
                function redirectTo(url) {
                    window.location.href = url;
                }
            </script>

            <div class="chart-container">
                <div class="chart-box">
                    <canvas id="TicketLineChart"></canvas>
                    <div id="chart-loader" class="loader"></div>
                </div>
            </div>
        </div>

    <div class="container">
        <h1 class="admin-title">ICT Forms</h1>
        <div class="card">
        <a href="ict_list.php" class="admin-form-card">
            <img src="./Images/ict_banner.jpg" alt="ICT Technical Assistance" class="admin-card-image">
            <div class="admin-card-content">
                <div class="admin-card-title">ICT Technical Assistance Form</div>
            </div>
        </a>

        <a href="DTS_list.php" class="admin-form-card">
            <img src="./Images/dts_banner.jpg" alt="Help Desk" class="admin-card-image">
            <div class="admin-card-content">
                <div class="admin-card-title">DTS Request Form</div>
            </div>
        </a>

        <a href="email_list.php" class="admin-form-card">
            <img src="./Images/email_banner.jpg" alt="Email Request" class="admin-card-image">
            <div class="admin-card-content">
                <div class="admin-card-title">Email Request Form</div>
            </div>
        </a>

        <a href="help_list.php" class="admin-form-card">
            <img src="./Images/help.jpg" alt="DTS Request" class="admin-card-image">
            <div class="admin-card-content">
                <div class="admin-card-title">Help Desk Form</div>
            </div>
        </a>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/analysis.js"></script>
    <script src="js/calendar.js"></script>

    <div class="gov-logos">
        <div class="logo-container">
            <img src="./Images/transparency.png" alt="Transparency Seal" style="width: 110px; height: auto;">
        </div>
        
        <div class="logo-container">
            <img src="./Images/depedlogo_noborder.png" alt="DepEd Matatag Logo" style="width: 140px; height: auto;">
        </div>
        
        <div class="logo-container">
            <img src="./Images/newpilipinaslogo_noborder.png" alt="Bagong Pilipinas Logo">
        </div>
        
        <div class="logo-container">
            <img src="./Images/freedom.png" alt="Freedom of Information Logo">
        </div>
    </div>
    <footer class="footer">
        <p>Copyright © <?php echo date("Y"); ?> Ticketing. All Rights Reserved</p>
        <div class="developer-credits">
            <p>Developed by: Angela Faith M. Salazar and Arien R. Peredo</p>
        </div>
    </footer>

<script src="js/confirmation.js"></script>
<script src="js/transition.js"></script>
</body>
</html>