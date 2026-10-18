<?php
require 'db.php';
$data = json_decode(file_get_contents("php://input"), true);
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM administrator WHERE Email = ?");
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

unset($user['PasswordHash']);
echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
