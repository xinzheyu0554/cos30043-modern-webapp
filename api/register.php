<?php
require_once "helpers.php";
require_once "db.php";

function myway_password_hash($password) {
    if (function_exists("password_hash")) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    $cost = 10;
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./";
    $salt = "";

    for ($i = 0; $i < 22; $i++) {
        $salt .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return crypt($password, sprintf("$2y$%02d$", $cost) . $salt);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    response(false, "Invalid request method", null, 405);
}

$data = getJsonInput();

$username = isset($data["username"]) ? trim($data["username"]) : "";
$email = isset($data["email"]) ? trim($data["email"]) : "";
$password = isset($data["password"]) ? $data["password"] : "";

if ($username === "" || $email === "" || $password === "") {
    response(false, "All fields are required", null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response(false, "Invalid email format", null, 400);
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/", $password)) {
    response(false, "Password must be at least 8 characters and include uppercase, lowercase, number, and special character", null, 400);
}

$stmt = mysqli_prepare($conn, "SELECT userId FROM `User` WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    response(false, "Email already exists", null, 409);
}

mysqli_stmt_close($stmt);

$hashedPassword = myway_password_hash($password);
$now = date("Y-m-d H:i:s");

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO `User` (username, email, password, role, isActive, createdAt)
     VALUES (?, ?, ?, 'user', 1, ?)"
);

mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashedPassword, $now);

if (!mysqli_stmt_execute($stmt)) {
    response(false, "Registration failed", mysqli_error($conn), 500);
}

$userId = mysqli_insert_id($conn);

$userData = array(
    "userId" => intval($userId),
    "username" => $username,
    "email" => $email,
    "role" => "user"
);

$token = createToken($userData);

response(true, "Registration successful", array(
    "token" => $token,
    "user" => $userData
));
?>
