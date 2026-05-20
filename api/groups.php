<?php
require_once "helpers.php";
require_once "db.php";


$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $id = intval($_GET["id"] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT g.*, u.username AS creatorName
             FROM `Group` g
             LEFT JOIN `User` u ON g.createdBy = u.userId
             WHERE g.groupId = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $group = mysqli_fetch_assoc($result);

        if (!$group) {
            response(false, "Group not found", null, 404);
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS memberCount
             FROM `Membership`
             WHERE groupId = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $countResult = mysqli_stmt_get_result($stmt);
        $countRow = mysqli_fetch_assoc($countResult);
        $group["memberCount"] = intval($countRow["memberCount"]);

        $token = getBearerToken();
        $user = verifyToken($token);
        $group["isMember"] = false;

        if ($user) {
            $userId = intval($user["userId"]);

            $stmt = mysqli_prepare(
                $conn,
                "SELECT membershipId
                 FROM `Membership`
                 WHERE groupId = ? AND userId = ?"
            );

            mysqli_stmt_bind_param($stmt, "ii", $id, $userId);
            mysqli_stmt_execute($stmt);

            $memberCheck = mysqli_stmt_get_result($stmt);
            $group["isMember"] = mysqli_fetch_assoc($memberCheck) ? true : false;
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT m.userId, u.username, m.joinedAt
             FROM `Membership` m
             LEFT JOIN `User` u ON m.userId = u.userId
             WHERE m.groupId = ?
             ORDER BY m.joinedAt ASC"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $membersResult = mysqli_stmt_get_result($stmt);
        $members = [];

        while ($row = mysqli_fetch_assoc($membersResult)) {
            $members[] = $row;
        }

        $group["members"] = $members;

        response(true, "Group loaded", $group);
    }

    $search = trim($_GET["search"] ?? "");
    $category = trim($_GET["category"] ?? "");

    $sql = "
        SELECT g.*, u.username AS creatorName,
               (SELECT COUNT(*) FROM `Membership` m WHERE m.groupId = g.groupId) AS memberCount
        FROM `Group` g
        LEFT JOIN `User` u ON g.createdBy = u.userId
        WHERE 1 = 1
    ";

    $params = [];
    $types = "";

    if ($search !== "") {
        $searchParam = "%" . $search . "%";
        $sql .= " AND (g.name LIKE ? OR g.description LIKE ?)";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }

    if ($category !== "") {
        $sql .= " AND g.category = ?";
        $params[] = $category;
        $types .= "s";
    }

    $sql .= " ORDER BY g.name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $groups = [];

    $token = getBearerToken();
    $user = verifyToken($token);

    while ($row = mysqli_fetch_assoc($result)) {
        $row["memberCount"] = intval($row["memberCount"]);
        $row["isMember"] = false;

        if ($user) {
            $userId = intval($user["userId"]);
            $groupId = intval($row["groupId"]);

            $memberStmt = mysqli_prepare(
                $conn,
                "SELECT membershipId
                 FROM `Membership`
                 WHERE groupId = ? AND userId = ?"
            );

            mysqli_stmt_bind_param($memberStmt, "ii", $groupId, $userId);
            mysqli_stmt_execute($memberStmt);

            $memberResult = mysqli_stmt_get_result($memberStmt);
            $row["isMember"] = mysqli_fetch_assoc($memberResult) ? true : false;
        }

        $groups[] = $row;
    }

    response(true, "Groups loaded", $groups);
}

if ($method === "POST") {
    $user = requireLogin();

    $data = getJsonInput();

    $groupId = intval($data["groupId"] ?? 0);
    $userId = intval($user["userId"]);

    if ($groupId <= 0) {
        response(false, "groupId is required", null, 400);
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT groupId FROM `Group` WHERE groupId = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $groupId);
    mysqli_stmt_execute($stmt);

    $groupCheck = mysqli_stmt_get_result($stmt);

    if (!mysqli_fetch_assoc($groupCheck)) {
        response(false, "Group not found", null, 404);
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT membershipId
         FROM `Membership`
         WHERE groupId = ? AND userId = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $groupId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $membership = mysqli_fetch_assoc($result);

    if ($membership) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM `Membership`
             WHERE groupId = ? AND userId = ?"
        );

        mysqli_stmt_bind_param($stmt, "ii", $groupId, $userId);
        mysqli_stmt_execute($stmt);

        response(true, "Left group", [
            "isMember" => false
        ]);
    }

    $now = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `Membership`
         (groupId, userId, joinedAt)
         VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "iis", $groupId, $userId, $now);

    if (!mysqli_stmt_execute($stmt)) {
        response(false, "Failed to join group", mysqli_error($conn), 500);
    }

    response(true, "Joined group", [
        "isMember" => true
    ]);
}

response(false, "Invalid request method", null, 405);
?>