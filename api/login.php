<?php
require_once "db.php";
require_once "helpers.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    response(false, "Invalid request method", null, 405);
}

$data = getJsonInput();

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($email === "" || $password === "") {
    response(false, "Email and password are required", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT userId, username, email, password, role 
     FROM `User` 
     WHERE email = ? AND isActive = 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user["password"])) {
    response(false, "Invalid email or password", null, 401);
}

$_SESSION["user"] = [
    "userId" => $user["userId"],
    "username" => $user["username"],
    "email" => $user["email"],
    "role" => $user["role"]
];

response(true, "Login successful", $_SESSION["user"]);
?>