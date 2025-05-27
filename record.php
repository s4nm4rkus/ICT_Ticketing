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
    <link rel="stylesheet" type="text/css" href="css/form.css" />
    <title>Generated Report</title>
    <style>
        .filter-item {
            display: flex;
            flex-direction: column; 
            margin-right: 15px; 
            margin-bottom: 10px; 
        }
        .filter-item label {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .filter-item input[type="date"],
        .filter-item select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .date-filter-container {
            display: flex;
            flex-wrap: wrap; 
            align-items: flex-end; 
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .date-filter-container button {
            margin-left: 10px;
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
    <div class="date-filter-container">
        <div class="filter-item">
            <label for="start">Start Date:</label>
            <input type="date" id="start" name="start">
        </div>
        <div class="filter-item">
            <label for="end">End Date:</label>
            <input type="date" id="end" name="end">
        </div>
        <div class="filter-item">
            <label for="sortTypeDropdown">Request Type:</label>
            <select id="sortTypeDropdown" name="request_type">
                <option value="All">All</option>
                <option value="ICT Technical Assistance">ICT Technical Assistance</option>
                <option value="DTS Request">DTS Request</option>
                <option value="Email Request">Email Request</option>
                <option value="Help Desk">Help Desk</option>
            </select>
        </div>
        <button id="filterButton" class="filter-btn">Apply Filters</button>
        <button id="resetButton" class="reset-btn">Reset Filters</button>
    </div>

    <div class="Recordpage">
            <div class="admin-header">
                <div class="admin-logo">
                    <img src="./Images/logo.jpg" alt="Logo" class="admin-logo-img">
                    <span class="admin-logo-text">SDO Tayabas City</span>
                </div>
                <a href="admin.php" class="admin-back-btn">Back</a>
            </div>

        <div class="record01-container">
            <div class="record-container">
                <div class="department-header">
                    <div class="department-logos-011">
                        <img src="./Images/kagawaran.png" alt="Logo 1" class="department-logo-011">
                        <img src="./Images/logo.png" alt="Logo 2" class="department-logo-011">
                    </div>
                    <div class="department-title">Republic of the Philippines</div>
                    <div class="department-subtitle">Department of Education</div>
                    <div class="department-title-01">REGION IV- A CALABARZON</div>
                    <div class="department-title-01">CITY SCHOOLS DIVISION OF THE CITY OF TAYABAS</div>
                </div>
                <hr>

                <table>
                    <thead>
                        <tr>
                            <th>Type of Request</th>
                            <th>REQUESTOR</th>
                            <th>Office/School</th>
                            <th>Request's Details</th>
                            <th>Date Requested</th>
                            <th>Date Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

                <div class="signature-section">
                    <div class="signature">
                        <p>Prepared by:</p>
                        <div class="signature-line"></div>
                        <p><strong>Mark Bryan P. Valencia</strong></p>
                        <p>Information Technology Officer I</p>
                    </div>
                    <div class="signature">
                        <p>Noted by:</p>
                        <div class="signature-line"></div>
                        <p><strong>Celedonio B. Balderas Jr.</strong></p>
                        <p>Schools Division Superintendent</p>
                    </div>
                </div>

                <div class="footer-container">
                    <div class="top-border"></div>
                    <div class="footer-01">
                        <div class="footer-left">
                            <img src="./Images/deped.png" alt="DepEd Logo" class="footer-logo">
                            <img src="./Images/pilipinas.png" alt="Bagong Pilipinas Logo" class="footer-logo">
                            <img src="./Images/logo.png" alt="Tayabas City Logo" class="footer-logo">
                        </div>
                        <div class="footer-info">
                            Address: Brgy. Potol, Tayabas City<br>
                            Telephone No.: (042) 785-9615<br>
                            Email Address: tayabas.city@deped.gov.ph<br>
                            Website: https://www.sdotayabascity.ph
                        </div>
                        <table class="record-table">
                            <tr>
                                <td>Doc. Ref. Code</td>
                                <td>SDO-OSDS-F020</td>
                                <td>Rev</td>
                                <td>00</td>
                            </tr>
                            <tr>
                                <td>Effectivity</td>
                                <td>01.08.24</td>
                                <td>Page</td>
                                <td>1 of 1</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <a href="#" class="print-btn" id="printButton">Print Form</a>

    <footer class="footer">
        <p>Copyright © 2025 Ticketing. All Rights Reserved</p>
        <div class="developer-credits">
            <p>Developed by: Angela Faith M. Salazar and Arien R. Peredo</p>
        </div>
    </footer>
    <script src="js/GenerateReport.js"></script>
</body>
</html>