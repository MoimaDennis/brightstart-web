<?php
require 'db.php';
$adminId = require_login();

$pdo->prepare("INSERT IGNORE INTO tblusersettings (AdminID) VALUES (?)")->execute([$adminId]);   // create defaults on first use
$stmt = $pdo->prepare("SELECT Theme, TextSize, ShowDashboardSlider FROM tblusersettings WHERE AdminID = ?");
$stmt->execute([$adminId]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

$u = $pdo->prepare("SELECT FullName, Email, Username, Role, AuthProvider FROM tbladministrator WHERE AdminID = ?");
$u->execute([$adminId]);

json_out([
    "success"  => true,
    "settings" => ["theme" => $s['Theme'], "text_size" => $s['TextSize'], "show_slider" => (int) $s['ShowDashboardSlider']],
    "profile"  => $u->fetch(PDO::FETCH_ASSOC)
]);