<?php
$db_host     = "localhost";
$db_user     = "root";
$db_password = "";
$db_name     = "BrightStart";
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("<p style='color:red;font-family:Arial;'><strong>Connection Failed:</strong> " . $conn->connect_error . "</p>");
}
$conn->set_charset("utf8");
?>