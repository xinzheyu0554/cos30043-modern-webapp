<?php
require_once "helpers.php";
require_once "db.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $id = intval($_GET["id"] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT c.*, u.username AS creatorName
             FROM `Content` c
             LEFT JOIN `User` u ON c.createdBy = u.userId
             WHERE c.contentId = ? AND c.isDeleted = 0"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $content = mysqli_fetch_assoc($result);

        response(true, "Content loaded", $content);
    }

    $search = trim($_GET["search"] ?? "");
    $category = trim($_GET["category"] ?? "");
    $sort = $_GET["sort"] ?? "newest";
    $page = max(1, intval($_GET["page"] ?? 1));
    $limit = max(1, intval($_GET["limit"] ?? 6));
    $offset = ($page - 1) * $limit;

    $orderBy = "c.createdAt DESC";

    if ($sort === "oldest") {
        $orderBy = "c.createdAt ASC";
    } elseif ($sort === "title") {
        $orderBy = "c.title ASC";
    }

    $sql = "
        SELECT c.*, u.username AS creatorName
        FROM `Content` c
        LEFT JOIN `User` u ON c.createdBy = u.userId
        WHERE c.isDeleted = 0
    ";

    if ($search !== "") {
        $safeSearch = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (
            c.title LIKE '%$safeSearch%' OR 
            c.category LIKE '%$safeSearch%' OR 
            c.body LIKE '%$safeSearch%'
        )";
    }

    if ($category !== "") {
        $safeCategory = mysqli_real_escape_string($conn, $category);
        $sql .= " AND c.category = '$safeCategory'";
    }

    $sql .= " ORDER BY $orderBy LIMIT $limit OFFSET $offset";

    $result = mysqli_query($conn, $sql);
    $contents = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $contents[] = $row;
    }

    response(true, "Contents loaded", $contents);
}

if ($method === "POST") {
    $user = requireStaffOrAdmin();

    $data = getJsonInput();

    $title = trim($data["title"] ?? "");
    $author = trim($data["author"] ?? "");
    $category = trim($data["category"] ?? "");
    $imageUrl = trim($data["imageUrl"] ?? "");
    $body = trim($data["body"] ?? "");

    if ($title === "" || $body === "") {
        response(false, "Title and body are required", null, 400);
    }

    $now = date("Y-m-d H:i:s");
    $createdBy = intval($user["userId"]);

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `Content`
         (title, author, category, imageUrl, body, createdBy, isDeleted, createdAt)
         VALUES (?, ?, ?, ?, ?, ?, 0, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssssis",
        $title,
        $author,
        $category,
        $imageUrl,
        $body,
        $createdBy,
        $now
    );

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to create content", mysqli_error($conn), 500);
    }

    response(true, "Content created");
}

if ($method === "PUT") {
    requireStaffOrAdmin();

    $data = getJsonInput();

    $contentId = intval($data["contentId"] ?? 0);
    $title = trim($data["title"] ?? "");
    $author = trim($data["author"] ?? "");
    $category = trim($data["category"] ?? "");
    $imageUrl = trim($data["imageUrl"] ?? "");
    $body = trim($data["body"] ?? "");

    if ($contentId <= 0 || $title === "" || $body === "") {
        response(false, "Invalid content data", null, 400);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `Content`
         SET title = ?, author = ?, category = ?, imageUrl = ?, body = ?, updatedAt = ?
         WHERE contentId = ? AND isDeleted = 0"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $title,
        $author,
        $category,
        $imageUrl,
        $body,
        $now,
        $contentId
    );

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to update content", mysqli_error($conn), 500);
    }

    response(true, "Content updated");
}

if ($method === "DELETE") {
    requireStaffOrAdmin();

    $data = getJsonInput();

    $contentId = intval($data["contentId"] ?? 0);

    if ($contentId <= 0) {
        response(false, "contentId is required", null, 400);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `Content`
         SET isDeleted = 1, updatedAt = ?
         WHERE contentId = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $now, $contentId);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to delete content", mysqli_error($conn), 500);
    }

    response(true, "Content deleted");
}

response(false, "Invalid request method", null, 405);
?>