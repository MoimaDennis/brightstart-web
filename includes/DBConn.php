<?php
require_once __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_OFF);   // show friendly error messages instead of a crash page
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($conn->connect_error) {
    die("<p style='color:red;font-family:Arial;'><strong>Connection Failed:</strong> Could not reach the database. Please try again later.</p>");
}
$conn->set_charset("utf8mb4");