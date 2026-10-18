<?php
require 'db.php';
require_once __DIR__ . '/../includes/validation.php';
$adminId = require_login();
$r = json_decode(file_get_contents("php://input"), true) ?: [];

$theme  = $r['theme']       ?? '';
$size   = $r['text_size']   ?? '';
$slider = $r['show_slider'] ?? '';
foreach (['theme' => $theme, 'text_size' => $size, 'show_slider' => $slider] as $k => $v) {
    if (!validate_setting($k, $v)) json_out(["success" => false, "message" => "Invalid value for $k"], 400);
}

$pdo->prepare(
    "INSERT INTO tblusersettings (AdminID, Theme, TextSize, ShowDashboardSlider) VALUES (?,?,?,?)
     ON DUPLICATE KEY UPDATE Theme = VALUES(Theme), TextSize = VALUES(TextSize), ShowDashboardSlider = VALUES(ShowDashboardSlider)"
)->execute([$adminId, $theme, $size, (int) $slider]);

json_out(["success" => true, "message" => "Settings saved"]);