<?php
require 'db.php';
$r    = json_decode(file_get_contents("php://input"), true);
$id   = $r['admin_id'] ?? 0;
$role = $r['role'] ?? 'Volunteer';
$pdo->prepare("UPDATE administrator SET Role=? WHERE AdminID=?")->execute([$role,$id]);
echo json_encode(["success"=>true,"message"=>"Role updated"]);
