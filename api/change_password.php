<?php
require 'db.php';
require_once __DIR__ . '/../includes/validation.php';
$adminId = require_login();
$r = json_decode(file_get_contents("php://input"), true) ?: [];

$current = $r['current_password'] ?? '';
$new     = $r['new_password'] ?? '';
$confirm = $r['confirm_password'] ?? '';

$stmt = $pdo->prepare("SELECT PasswordHash, AuthProvider FROM tbladministrator WHERE AdminID = ?");
$stmt->execute([$adminId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['AuthProvider'] !== 'local')              json_out(["success" => false, "message" => "Google accounts sign in with Google, so there is no password to change."], 400);
if (!password_verify($current, $user['PasswordHash'])) json_out(["success" => false, "message" => "Your current password is incorrect."], 400);
if ($err = validate_password($new))                  json_out(["success" => false, "message" => $err], 400);
if ($new !== $confirm)                               json_out(["success" => false, "message" => "The new passwords do not match."], 400);

$pdo->prepare("UPDATE tbladministrator SET PasswordHash = ? WHERE AdminID = ?")
    ->execute([password_hash($new, PASSWORD_BCRYPT), $adminId]);
json_out(["success" => true, "message" => "Password changed successfully"]);