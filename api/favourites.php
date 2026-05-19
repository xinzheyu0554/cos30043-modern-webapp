<?php
require_once "db.php";
require_once "helpers.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $user = requireLogin();
    $userId = intval($user["userId"]);
    $contentId = intval($_GET["contentId"] ?? 0);

    if ($contentId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT favouriteId
             FROM `Favourite`
             WHERE userId = ? AND contentId = ?"
        );

        mysqli_stmt_bind_param($stmt, "ii", $userId, $contentId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $favourite = mysqli_fetch_assoc($result);

        response(true, "Favourite status loaded", [
            "isFavourite" => $favourite ? true : false
        ]);
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT c.*
         FROM `Favourite` f
         JOIN `Content` c ON f.contentId = c.contentId
         WHERE f.userId = ? AND c.isDeleted = 0
         ORDER BY f.createdAt DESC"
    );

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $favourites = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $favourites[] = $row;
    }

    response(true, "Favourites loaded", $favourites);
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
        "SELECT favouriteId
         FROM `Favourite`
         WHERE contentId = ? AND userId = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $contentId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $favourite = mysqli_fetch_assoc($result);

    if ($favourite) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM `Favourite`
             WHERE contentId = ? AND userId = ?"
        );

        mysqli_stmt_bind_param($stmt, "ii", $contentId, $userId);
        mysqli_stmt_execute($stmt);

        response(true, "Favourite removed", [
            "isFavourite" => false
        ]);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `Favourite`
         (contentId, userId, createdAt)
         VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "iis", $contentId, $userId, $now);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to add favourite", mysqli_error($conn), 500);
    }

    response(true, "Favourite added", [
        "isFavourite" => true
    ]);
}

response(false, "Invalid request method", null, 405);
?>