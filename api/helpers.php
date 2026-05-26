<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

define("TOKEN_SECRET", "change_this_to_a_random_secret_key");

if (!function_exists("hash_equals")) {
    function hash_equals($knownString, $userString) {
        if (!is_string($knownString) || !is_string($userString)) {
            return false;
        }

        if (strlen($knownString) !== strlen($userString)) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < strlen($knownString); $i++) {
            $result |= ord($knownString[$i]) ^ ord($userString[$i]);
        }

        return $result === 0;
    }
}

if (!function_exists("password_hash")) {
    if (!defined("PASSWORD_DEFAULT")) {
        define("PASSWORD_DEFAULT", 1);
    }

    function password_hash($password, $algo, $options = array()) {
        $cost = isset($options["cost"]) ? intval($options["cost"]) : 10;

        if ($cost < 4) {
            $cost = 4;
        }

        if ($cost > 31) {
            $cost = 31;
        }

        $salt = "";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./";

        for ($i = 0; $i < 22; $i++) {
            $salt .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        $prefix = sprintf("$2y$%02d$", $cost);

        return crypt($password, $prefix . $salt);
    }
}

if (!function_exists("password_verify")) {
    function password_verify($password, $hash) {
        return crypt($password, $hash) === $hash;
    }
}

function getJsonInput() {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        return array();
    }

    return $data;
}

function response($success, $message, $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode(array(
        "success" => $success,
        "message" => $message,
        "data" => $data
    ));
    exit;
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, "-_", "+/"));
}

function createToken($user) {
    $role = isset($user["role"]) ? $user["role"] : "user";
    $expiryInSeconds = 60 * 60 * 24;

    if ($role === "user") {
        $expiryInSeconds = 60 * 60 * 24 * 7;
    }

    $header = base64UrlEncode(json_encode(array(
        "alg" => "HS256",
        "typ" => "JWT"
    )));

    $payload = base64UrlEncode(json_encode(array(
        "userId" => intval($user["userId"]),
        "username" => $user["username"],
        "email" => $user["email"],
        "role" => $user["role"],
        "exp" => time() + $expiryInSeconds
    )));

    $signature = base64UrlEncode(hash_hmac(
        "sha256",
        $header . "." . $payload,
        TOKEN_SECRET,
        true
    ));

    return $header . "." . $payload . "." . $signature;
}

function getBearerToken() {
    $headers = function_exists("getallheaders") ? getallheaders() : array();

    if (isset($headers["X-Auth-Token"])) {
        return trim($headers["X-Auth-Token"]);
    }

    if (isset($headers["x-auth-token"])) {
        return trim($headers["x-auth-token"]);
    }

    if (isset($_SERVER["HTTP_X_AUTH_TOKEN"])) {
        return trim($_SERVER["HTTP_X_AUTH_TOKEN"]);
    }

    if (isset($headers["Authorization"])) {
        $authHeader = $headers["Authorization"];

        if (strpos($authHeader, "Bearer ") === 0) {
            return trim(substr($authHeader, 7));
        }
    }

    if (isset($headers["authorization"])) {
        $authHeader = $headers["authorization"];

        if (strpos($authHeader, "Bearer ") === 0) {
            return trim(substr($authHeader, 7));
        }
    }

    if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
        $authHeader = $_SERVER["HTTP_AUTHORIZATION"];

        if (strpos($authHeader, "Bearer ") === 0) {
            return trim(substr($authHeader, 7));
        }
    }

    if (isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
        $authHeader = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];

        if (strpos($authHeader, "Bearer ") === 0) {
            return trim(substr($authHeader, 7));
        }
    }

    return null;
}

function verifyToken($token) {
    if (!$token) {
        return null;
    }

    $parts = explode(".", $token);

    if (count($parts) !== 3) {
        return null;
    }

    $header = $parts[0];
    $payload = $parts[1];
    $signature = $parts[2];

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