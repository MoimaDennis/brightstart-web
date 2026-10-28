<?php
require 'db.php';
$data = json_decode(file_get_contents("php://input"), true) ?: [];
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tbladministrator WHERE Email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "message" => "No account found with that email"]);
    exit();
}

if (!password_verify($password, $user['PasswordHash'])) {
    echo json_encode(["success" => false, "message" => "Incorrect password"]);
    exit();
}

if ($user['Verified'] == 0) {
    echo json_encode(["success" => false, "message" => "Your account is pending administrator approval"]);
    exit();
}

// Start a session so the mobile app (which keeps the session cookie) can call the settings endpoints
if (session_status() === PHP_SESSION_NONE) session_start();
session_regenerate_id(true);
$_SESSION['admin_id']    = $user['AdminID'];
$_SESSION['admin_name']  = $user['FullName'];
$_SESSION['admin_email'] = $user['Email'];
$_SESSION['admin_role']  = $user['Role'];

unset($user['PasswordHash']);
echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
