<?php
require_once "db.php";
require_once "helpers.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $contentId = intval($_GET["contentId"] ?? 0);

    if ($contentId <= 0) {
        response(false, "contentId is required", null, 400);
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT cm.*, u.username
         FROM `Comment` cm
         JOIN `User` u ON cm.userId = u.userId
         WHERE cm.contentId = ? AND cm.isDeleted = 0
         ORDER BY cm.createdAt ASC"
    );

    mysqli_stmt_bind_param($stmt, "i", $contentId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $comments = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }

    response(true, "Comments loaded", $comments);
}

if ($method === "POST") {
    $user = requireLogin();

    $data = getJsonInput();

    $contentId = intval($data["contentId"] ?? 0);
    $parentId = isset($data["parentId"]) ? intval($data["parentId"]) : null;
    $message = trim($data["message"] ?? "");

    if ($contentId <= 0 || $message === "") {
        response(false, "contentId and message are required", null, 400);
    }

    $now = date("Y-m-d H:i:s");
    $userId = intval($user["userId"]);

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `Comment`
         (contentId, userId, parentId, message, isDeleted, createdAt)
         VALUES (?, ?, ?, ?, 0, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iiiss",
        $contentId,
        $userId,
        $parentId,
        $message,
        $now
    );

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to create comment", mysqli_error($conn), 500);
    }

    response(true, "Comment created");
}

if ($method === "DELETE") {
    requireStaffOrAdmin();

    $data = getJsonInput();

    $commentId = intval($data["commentId"] ?? 0);

    if ($commentId <= 0) {
        response(false, "commentId is required", null, 400);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `Comment`
         SET isDeleted = 1, updatedAt = ?
         WHERE commentId = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $now, $commentId);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to delete comment", mysqli_error($conn), 500);
    }

    response(true, "Comment deleted");
}

response(false, "Invalid request method", null, 405);
?>