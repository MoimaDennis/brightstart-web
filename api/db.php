<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . '/../includes/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Small helpers used by the newer endpoints
function json_out($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr);
    exit();
}
function logged_in_admin_id() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['admin_id'] ?? null;
}
function require_login() {
    $id = logged_in_admin_id();
    if (!$id) json_out(["success" => false, "message" => "Please log in first"], 401);
    return (int) $id;
}