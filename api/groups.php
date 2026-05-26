<?php

//database connection and also the helper functions included as well
require_once "db.php";
require_once "helpers.php";


$method = $_SERVER["REQUEST_METHOD"];
//helps get all groups by ID as made for SQL database and checks by ID
if ($method === "GET") {
    $id = intval(isset($_GET["id"]) ? $_GET["id"] : 0);

    if ($id > 0) {
        //will fetch group along with the username of user
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
        //counts how many members the group has 
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
        //check if current logged in user is a member of this group 
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
            //if user is a member then will show in the row
            $memberCheck = mysqli_stmt_get_result($stmt);
            $group["isMember"] = mysqli_fetch_assoc($memberCheck) ? true : false;
        }
        //fecth the list of members in the group. in the detail page
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
        //loops through each member row and added to array as well 
        while ($row = mysqli_fetch_assoc($membersResult)) {
            $members[] = $row;
        }

        $group["members"] = $members;

        response(true, "Group loaded", $group);
    }
    //list all groups also has optional search and category filter 
    $search = trim(isset($_GET["search"]) ? $_GET["search"] : "");
    $category = trim(isset($_GET["category"]) ? $_GET["category"] : "");

    $sql = "
        SELECT g.*, u.username AS creatorName,
               (SELECT COUNT(*) FROM `Membership` m WHERE m.groupId = g.groupId) AS memberCount
        FROM `Group` g
        LEFT JOIN `User` u ON g.createdBy = u.userId
        WHERE 1 = 1
    ";
    //build dynamic query parameters 

    $params = [];
    $types = "";
    //if search term is provided then this will help filter by name or description even
    if ($search !== "") {
        $searchParam = "%" . $search . "%";
        $sql .= " AND (g.name LIKE ? OR g.description LIKE ?)";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }
    //if category provided then this will filter by exact category match
    if ($category !== "") {
        $sql .= " AND g.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    //sort alphabetically by group name
    $sql .= " ORDER BY g.name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    //help prepare and bind parameters
    if (!empty($params)) {
        $bindParams = [];
        $bindParams[] = $types;

        for ($i = 0; $i < count($params); $i++) {
            $bindParams[] = &$params[$i];
        }

        call_user_func_array(array($stmt, "bind_param"), $bindParams);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $groups = [];
    //check of current user is logged in (to determine membership status)
    $token = getBearerToken();
    $user = verifyToken($token);
    //loops through each group and check membership status
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
//POST for joining and leaving group toggle
if ($method === "POST") {
    $user = requireLogin();
    //read JSON sent from the front end
    $data = getJsonInput();

    $groupId = intval(isset($data["groupId"]) ? $data["groupId"] : 0);
    $userId = intval($user["userId"]);
    //validate that a group id was provided as in the 1,2,3 etc of each group
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
    //check if user is already a member of this group
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
    //if member leaves the group then membership will be deleted in the row
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
    //and if not a member and they join a group a new membership row will be created
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
//if request method is not get or post then return an error!
response(false, "Invalid request method", null, 405);
?>