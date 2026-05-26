<?php
require_once "helpers.php";
require_once "db.php";

function myway_password_verify($password, $hash) {
    if (function_exists("password_verify")) {
        return password_verify($password, $hash);
    }

    if ($hash === null || $hash === "") {
        return false;
    }

    return crypt($password, $hash) === $hash;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    response(false, "Invalid request method", null, 405);
}

$data = getJsonInput();

$email = isset($data["email"]) ? trim($data["email"]) : "";
$password = isset($data["password"]) ? $data["password"] : "";

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

if (!$user || !myway_password_verify($password, $user["password"])) {
    response(false, "Invalid email or password", null, 401);
}

$userData = array(
    "userId" => intval($user["userId"]),
    "username" => $user["username"],
    "email" => $user["email"],
    "role" => $user["role"]
);

$token = createToken($userData);

response(true, "Login successful", array(
    "token" => $token,
    "user" => $userData
));
?>
