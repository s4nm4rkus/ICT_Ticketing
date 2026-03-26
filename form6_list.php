<?php
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: login.html"); 
    exit();
}

if (!isset($_SESSION['role'])) {
    echo json_encode([]);
    exit;
}

$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/list.css" />
    <title>Form 6 - Application for Leave</title>
</head>

<body>
    <div id="loader-overlay">
        <!-- <p class="loader-text">Initializing...</p> -->
        <div class="boxes-loader">
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <script>
    const USER_ROLE = "<?php echo $_SESSION['role']; ?>";
    </script>
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
            <a href="help_list.php" class="arrow-button arrow-left">
                <img src="./Images/left-chevron.png" alt="Left Arrow">
            </a>
            <span class="title">Form 6 - Application for Leave List</span>
            <a href="DTS_list.php" class="arrow-button arrow-right">
                <img src="./Images/right-chevron.png" alt="Right Arrow">
            </a>
        </div>

        <div class="section recommendation-section">
            <div class="section-title">For Recommendation</div>
            <div class="section-content">
                <div class="box-header">For Action: Recommending Authorities</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Type of Leave</th>
                            <th>Inclusive Days</th>
                            <th>Date Reported</th>
                            <th>Office/Unit</th>
                        </tr>
                    </thead>
                    <tbody class="recommendation-tickets"></tbody>
                </table>
            </div>
        </div>

        <div class="section records-section">
            <div class="section-title">Records Unit</div>
            <div class="section-content">
                <div class="box-header">For Action: Records Unit</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Type of Leave</th>
                            <th>Inclusive Days</th>
                            <th>Date Reported</th>
                            <th>Office/Unit</th>
                        </tr>
                    </thead>
                    <tbody class="records-tickets"></tbody>
                </table>
            </div>
        </div>

        <div class="section personnel-section">
            <div class="section-title">Personnel Unit</div>
            <div class="section-content">
                <div class="box-header">For Action: Personnel Unit</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Type of Leave</th>
                            <th>Inclusive Days</th>
                            <th>Date Reported</th>
                            <th>Office/Unit</th>
                        </tr>
                    </thead>
                    <tbody class="personnel-tickets"></tbody>
                </table>
            </div>
        </div>
        <div class="section admin-section">
            <div class="section-title">Administrative Unit</div>
            <div class="section-content">
                <div class="box-header">For Action: Administrative Unit</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Type of Leave</th>
                            <th>Inclusive Days</th>
                            <th>Date Reported</th>
                            <th>Office/Unit</th>
                        </tr>
                    </thead>
                    <tbody class="admin-tickets"></tbody>
                </table>
            </div>
        </div>
        <div class="section sds-section">
            <div class="section-title">SDS/ASDS/Records Unit</div>
            <div class="section-content">
                <div class="box-header">For Action: SDS/ASDS/Records Unit</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Type of Leave</th>
                            <th>Inclusive Days</th>
                            <th>Date Reported</th>
                            <th>Office/Unit</th>
                        </tr>
                    </thead>
                    <tbody class="sds-tickets"></tbody>
                </table>
            </div>
        </div>
        <div class="section processed-section">
            <div class="section-title">Processed</div>
            <div class="section-content">
                <div class="box-header">Processed Form 6</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Type of Leave</th>
                            <th>Inclusive Days</th>
                            <th>Date Reported</th>
                            <th>Remarks</th>
                            <th>Office/Unit</th>
                        </tr>
                    </thead>
                    <tbody class="approved-tickets"></tbody>
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
        <!-- <div class="developer-credits">
            <p>Developed by: Angela Faith M. Salazar and Arien R. Peredo</p>
        </div> -->
    </footer>
    <script src="js/form6_list.js"></script>
</body>

</html>