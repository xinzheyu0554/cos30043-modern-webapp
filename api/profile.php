<?php
require_once "helpers.php";
require_once "db.php";

$user = requireLogin();
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $userId = intval($user["userId"]);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT userId, username, email, role, isActive, createdAt, updatedAt
         FROM `User`
         WHERE userId = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $profile = mysqli_fetch_assoc($result);

    response(true, "Profile loaded", $profile);
}

if ($method === "PUT") {
    $data = getJsonInput();

    $userId = intval($user["userId"]);
    $username = trim($data["username"] ?? "");
    $email = trim($data["email"] ?? "");

    if ($username === "" || $email === "") {
        response(false, "Username and email are required", null, 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        response(false, "Invalid email format", null, 400);
    }

    $check = mysqli_prepare(
        $conn,
        "SELECT userId FROM `User` WHERE email = ? AND userId != ?"
    );

    mysqli_stmt_bind_param($check, "si", $email, $userId);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        response(false, "Email already exists", null, 409);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `User`
         SET username = ?, email = ?, updatedAt = ?
         WHERE userId = ?"
    );

    mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $now, $userId);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to update profile", mysqli_error($conn), 500);
    }

    response(true, "Profile updated");
}

response(false, "Invalid request method", null, 405);
?>