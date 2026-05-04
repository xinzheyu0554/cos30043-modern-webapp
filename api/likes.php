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
        "SELECT COUNT(*) AS totalLikes
         FROM `ContentLike`
         WHERE contentId = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $contentId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    response(true, "Likes loaded", $data);
}

if ($method === "POST") {
    $user = requireLogin();

    $data = getJsonInput();

    $contentId = intval($data["contentId"] ?? 0);
    $userId = intval($user["userId"]);

    if ($contentId <= 0) {
        response(false, "contentId is required", null, 400);
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT likeId
         FROM `ContentLike`
         WHERE contentId = ? AND userId = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $contentId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $like = mysqli_fetch_assoc($result);

    if ($like) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM `ContentLike`
             WHERE contentId = ? AND userId = ?"
        );

        mysqli_stmt_bind_param($stmt, "ii", $contentId, $userId);
        mysqli_stmt_execute($stmt);

        response(true, "Like removed");
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `ContentLike`
         (contentId, userId, createdAt)
         VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "iis", $contentId, $userId, $now);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to like content", mysqli_error($conn), 500);
    }

    response(true, "Content liked");
}

response(false, "Invalid request method", null, 405);
?>