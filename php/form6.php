<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ticket";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(["success" => false, "message" => "No ID provided."]);
    exit;
}

$sql = "SELECT * FROM form6_applicationforleave WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();

    // Prepare consistent data structure for JS
   $response = [
    "success" => true,
    "ticket" => [
        "id" => $ticket["id"],
        "department" => $ticket["department"],
        "last_name" => $ticket["last_name"],
        "first_name" => $ticket["first_name"],
        "middle_name" => $ticket["middle_name"],
        "date_of_filing" => $ticket["date_of_filing"],
        "position" => $ticket["position"],
        "salary" => $ticket["salary"],
        "typeofleave_A" => $ticket["typeofleave_A"],
        "other_type_of_leave" => $ticket["other_type_of_leave"], // ✅ ADD THIS
        "specification_of_leave" => $ticket["specification_of_leave"],
        "other_purpose" => $ticket["other_purpose"], // ✅ ADD THIS
        "communication" => $ticket["communication"],
        "number_of_days_applied" => $ticket["number_of_days_applied"],
        "inclusive_days" => $ticket["inclusive_days"],
        "name_of_official" => $ticket["name_of_official"],
        "signatory_position" => $ticket["signatory_position"],
        "e_signature" => $ticket["e_signature"], // ✅ ADD THIS
        "status" => $ticket["status"] ?? "Pending"
    ]
];


    echo json_encode($response);
} else {
    echo json_encode(["success" => false, "message" => "No record found for ID: $id"]);
}

$stmt->close();
$conn->close();
?>
