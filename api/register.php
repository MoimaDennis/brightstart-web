<?php
require 'db.php';
$data     = json_decode(file_get_contents("php://input"), true);
$fullName = trim($data['full_name'] ?? '');
$email    = trim($data['email'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$role     = $data['role'] ?? 'Volunteer';

if (!$fullName || !$email || !$username || !$password) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit();
}

// Check duplicate
$check = $pdo->prepare("SELECT AdminID FROM administrator WHERE Email=? OR Username=?");
$check->execute([$email, $username]);
if ($check->fetch()) {
    echo json_encode(["success" => false, "message" => "Email or username already exists"]);
    exit();
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO administrator (FullName,Email,Username,PasswordHash,Role,Verified) VALUES (?,?,?,?,?,0)");
$stmt->execute([$fullName, $email, $username, $hash, $role]);
echo json_encode(["success" => true, "message" => "Account created. Please wait for administrator approval before logging in."]);
