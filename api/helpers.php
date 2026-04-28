<?php
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

function getJsonInput() {
    return json_decode(file_get_contents("php://input"), true);
}

function response($success, $message, $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

function requireLogin() {
    if (!isset($_SESSION["user"])) {
        response(false, "Authentication required", null, 401);
    }

    return $_SESSION["user"];
}

function requireAdmin() {
    $user = requireLogin();

    if ($user["role"] !== "admin") {
        response(false, "Admin permission required", null, 403);
    }

    return $user;
}

function requireStaffOrAdmin() {
    $user = requireLogin();

    if ($user["role"] !== "admin" && $user["role"] !== "adminstaff") {
        response(false, "Staff or admin permission required", null, 403);
    }

    return $user;
}
?>