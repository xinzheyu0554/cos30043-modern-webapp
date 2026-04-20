<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php";

$sql = "SELECT userId, firstName, lastName, email, role, address, phoneNumber, isActive, createdAt, updatedAt FROM `User` ORDER BY userId ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch users"
    ]);
    exit;
}

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $users
]);

mysqli_free_result($result);
mysqli_close($conn);
?>
