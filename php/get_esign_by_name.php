<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$conn = new mysqli("localhost","root","","ticket");

$name = $_GET['name'] ?? '';

if(!$name){
    echo json_encode(["success"=>false,"error"=>"Missing name"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT signature_file
    FROM department_authorizers
    WHERE officer_name = ?
    LIMIT 1
");
$stmt->bind_param("s",$name);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode([
        "success"=>true,
        "file"=>$row["signature_file"]
    ]);
}else{
    echo json_encode([
        "success"=>false,
        "error"=>"No signature found"
    ]);
}

$stmt->close();
$conn->close();