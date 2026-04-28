<?php
require_once "helpers.php";

session_destroy();

response(true, "Logout successful");
?>