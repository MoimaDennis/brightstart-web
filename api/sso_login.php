<?php
// Google single sign-on: the browser signs in with Firebase, then sends us the ID token.
require 'db.php';
require_once __DIR__ . '/../includes/sso.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$data    = json_decode(file_get_contents("php://input"), true);
$idToken = trim($data['idToken'] ?? '');
if ($idToken === '') json_out(["success" => false, "message" => "No sign-in token received"], 400);

$info = verify_firebase_token($idToken);
if (!$info || !$info['verified']) json_out(["success" => false, "message" => "Google sign-in could not be verified"], 401);

$stmt = $pdo->prepare("SELECT AdminID, FullName, Email, Role, Verified FROM tbladministrator WHERE Email = ?");
$stmt->execute([$info['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // First time with Google: create a Volunteer account that waits for admin approval
    $base = preg_replace('/[^a-z0-9._-]/', '', strtolower(strstr($info['email'], '@', true)));
    $base = str_pad($base, 4, '0');
    $username = $base;
    $check = $pdo->prepare("SELECT 1 FROM tbladministrator WHERE Username = ?");
    for ($i = 1; ; $i++) {
        $check->execute([$username]);
        if (!$check->fetch()) break;
        $username = $base . $i;
    }
    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);   // Google users never use this
    $pdo->prepare("INSERT INTO tbladministrator (FullName, Email, Username, PasswordHash, Role, Verified, AuthProvider) VALUES (?,?,?,?,'Volunteer',0,'google')")
        ->execute([$info['name'], $info['email'], $username, $hash]);
    json_out(["success" => false, "pending" => true,
              "message" => "Account created with Google. An administrator must approve it before you can sign in."], 403);
}

if ((int) $user['Verified'] !== 1) {
    json_out(["success" => false, "pending" => true, "message" => "Your account is pending administrator approval."], 403);
}

session_regenerate_id(true);
$_SESSION['admin_id']    = $user['AdminID'];
$_SESSION['admin_name']  = $user['FullName'];
$_SESSION['admin_email'] = $user['Email'];
$_SESSION['admin_role']  = $user['Role'];
json_out(["success" => true, "message" => "Signed in with Google", "redirect" => "dashboard.php"]);