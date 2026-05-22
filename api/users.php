<?php
require_once "db.php";
require_once "helpers.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    requireAdmin();

    $sql = "
        SELECT userId, username, email, role, isActive, createdAt, updatedAt
        FROM `User`
        ORDER BY userId DESC
    ";

    $result = mysqli_query($conn, $sql);
    $users = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    response(true, "Users loaded", $users);
}

if ($method === "PUT") {
    requireAdmin();

    $data = getJsonInput();

    $userId = intval($data["userId"] ?? 0);
    $action = $data["action"] ?? "";
    $now = date("Y-m-d H:i:s");

    if ($userId <= 0) {
        response(false, "userId is required", null, 400);
    }

    if ($action === "updateRole") {
        $role = $data["role"] ?? "";

        if (!in_array($role, ["user", "adminstaff"])) {
            response(false, "Invalid role", null, 400);
        }

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE `User`
             SET role = ?, updatedAt = ?
             WHERE userId = ? AND role != 'admin'"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $role, $now, $userId);

        if (!mysqli_stmt_execute($stmt)) {
            response(false, "Failed to update user role", mysqli_error($conn), 500);
        }

        response(true, "User role updated");
    }

    if ($action === "restore") {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE `User`
             SET isActive = 1, updatedAt = ?
             WHERE userId = ? AND role != 'admin'"
        );

        mysqli_stmt_bind_param($stmt, "si", $now, $userId);

        if (!mysqli_stmt_execute($stmt)) {
            response(false, "Failed to restore user", mysqli_error($conn), 500);
        }

        response(true, "User restored");
    }

    response(false, "Invalid action", null, 400);
}

if ($method === "DELETE") {
    requireAdmin();

    $data = getJsonInput();

    $userId = intval($data["userId"] ?? 0);

    if ($userId <= 0) {
        response(false, "userId is required", null, 400);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `User`
         SET isActive = 0, updatedAt = ?
         WHERE userId = ? AND role != 'admin'"
    );

    mysqli_stmt_bind_param($stmt, "si", $now, $userId);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to deactivate user", mysqli_error($conn), 500);
    }

    response(true, "User deactivated");
}

response(false, "Invalid request method", null, 405);
?>