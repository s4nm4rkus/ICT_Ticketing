<?php
$conn = new mysqli('localhost', 'u164243783_ticket', '^^_ICTHOSTING2025_mysqluserpassword', 'u164243783_ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pendingQuery = [
    "dts" => "SELECT COUNT(*) AS count FROM dtsrequest WHERE status = 'Pending'",
    "email" => "SELECT COUNT(*) AS count FROM emailrequest WHERE status = 'Pending'",
    "help" => "SELECT COUNT(*) AS count FROM helpdesk WHERE status = 'Pending'",
    "ict" => "SELECT COUNT(*) AS count FROM icttechnicalassistance WHERE status = 'Pending'",
];

$pendingData = [];

foreach ($pendingQuery as $key => $sql) {
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $pendingData[$key] = $row['count'];
}


$tables = ['dtsrequest', 'emailrequest', 'helpdesk', 'icttechnicalassistance'];
$tableKeys = ['dts', 'email', 'help', 'ict'];
$currentYear = date('Y');

$quarterlyData = [
    'dts' => [0, 0, 0, 0],
    'email' => [0, 0, 0, 0],
    'help' => [0, 0, 0, 0],
    'ict' => [0, 0, 0, 0]
];

for ($i = 0; $i < count($tables); $i++) {
    $table = $tables[$i];
    $key = $tableKeys[$i];
    
    $dateColumn = 'date';
    
    $checkColumn = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'date'");
    if ($checkColumn->num_rows == 0) {
        $alternatives = ['date_filed', 'date_reported'];
        foreach ($alternatives as $alt) {
            $checkAlt = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$alt'");
            if ($checkAlt->num_rows > 0) {
                $dateColumn = $alt;
                break;
            }
        }
    }
    
    for ($quarter = 1; $quarter <= 4; $quarter++) {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;
        
        $sql = "SELECT COUNT(*) AS count FROM `$table` 
                WHERE YEAR($dateColumn) = $currentYear 
                AND MONTH($dateColumn) BETWEEN $startMonth AND $endMonth";
        
        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            $quarterlyData[$key][$quarter - 1] = (int)$row['count'];
        } else {
            error_log("Error querying $table: " . $conn->error);
        }
    }
}

$conn->close();

$data = [
    'pending' => $pendingData,
    'quarterly' => $quarterlyData
];

echo json_encode($data);
?>