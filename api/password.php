<?php
require_once "helpers.php";
require_once "db.php";

$user = requireLogin();
$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "PUT") {
    response(false, "Invalid request method", null, 405);
}

$data = getJsonInput();

$currentPassword = $data["currentPassword"] ?? "";
$newPassword = $data["newPassword"] ?? "";
$confirmPassword = $data["confirmPassword"] ?? "";
$userId = intval($user["userId"]);

if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
    response(false, "All password fields are required", null, 400);
}

if ($newPassword !== $confirmPassword) {
    response(false, "New password and confirmation do not match", null, 400);
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/", $newPassword)) {
    response(false, "New password must be at least 8 characters and include uppercase, lowercase, number, and special character", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT password
     FROM `User`
     WHERE userId = ? AND isActive = 1"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$account = mysqli_fetch_assoc($result);

if (!$account || !password_verify($currentPassword, $account["password"])) {
    response(false, "Current password is incorrect", null, 401);
}

if (password_verify($newPassword, $account["password"])) {
    response(false, "New password must be different from the current password", null, 400);
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$now = date("Y-m-d H:i:s");

$updateStmt = mysqli_prepare(
    $conn,
    "UPDATE `User`
     SET password = ?, updatedAt = ?
     WHERE userId = ?"
);

mysqli_stmt_bind_param($updateStmt, "ssi", $hashedPassword, $now, $userId);

if (!mysqli_stmt_execute($updateStmt)) {
    response(false, "Failed to update password", mysqli_error($conn), 500);
}

response(true, "Password updated successfully");
?>
