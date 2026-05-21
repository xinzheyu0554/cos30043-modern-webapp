<?php

$host = "feenix-mariadb.swin.edu.au";
$user = "s105385294";
$pswd = "311292";
$dbnm = "s105385294_db";

$conn = @mysqli_connect($host, $user, $pswd, $dbnm);

if (!$conn) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "db_error" => mysqli_connect_error()
    ]);
    exit;
}

mysqli_set_charset($conn, "utf8");
?>