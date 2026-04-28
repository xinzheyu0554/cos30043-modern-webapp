<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

define("TOKEN_SECRET", "change_this_to_a_random_secret_key");

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

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, "-_", "+/"));
}

function createToken($user) {
    $header = base64UrlEncode(json_encode([
        "alg" => "HS256",
        "typ" => "JWT"
    ]));

    $payload = base64UrlEncode(json_encode([
        "userId" => intval($user["userId"]),
        "username" => $user["username"],
        "email" => $user["email"],
        "role" => $user["role"],
        "exp" => time() + (60 * 60 * 24)
    ]));

    $signature = base64UrlEncode(hash_hmac(
        "sha256",
        $header . "." . $payload,
        TOKEN_SECRET,
        true
    ));

    return $header . "." . $payload . "." . $signature;
}

function getBearerToken() {
    $headers = getallheaders();

    if (!isset($headers["Authorization"])) {
        return null;
    }

    $authHeader = $headers["Authorization"];

    if (strpos($authHeader, "Bearer ") !== 0) {
        return null;
    }

    return trim(substr($authHeader, 7));
}

function verifyToken($token) {
    if (!$token) {
        return null;
    }

    $parts = explode(".", $token);

    if (count($parts) !== 3) {
        return null;
    }

    [$header, $payload, $signature] = $parts;

    $expectedSignature = base64UrlEncode(hash_hmac(
        "sha256",
        $header . "." . $payload,
        TOKEN_SECRET,
        true
    ));

    if (!hash_equals($expectedSignature, $signature)) {
        return null;
    }

    $payloadData = json_decode(base64UrlDecode($payload), true);

    if (!$payloadData || !isset($payloadData["exp"]) || $payloadData["exp"] < time()) {
        return null;
    }

    return $payloadData;
}

function requireLogin() {
    $token = getBearerToken();
    $user = verifyToken($token);

    if (!$user) {
        response(false, "Authentication required", null, 401);
    }

    return $user;
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