<?php
require 'db.php';
require_once __DIR__ . '/../includes/validation.php';
$adminId = require_login();
$r = json_decode(file_get_contents("php://input"), true) ?: [];

$name = trim($r['full_name'] ?? '');
if ($err = validate_full_name($name)) json_out(["success" => false, "message" => $err], 400);

$pdo->prepare("UPDATE tbladministrator SET FullName = ? WHERE AdminID = ?")->execute([$name, $adminId]);
$_SESSION['admin_name'] = $name;
json_out(["success" => true, "message" => "Profile updated"]);