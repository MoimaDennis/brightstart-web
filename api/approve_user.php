<?php
require 'db.php';
$r  = json_decode(file_get_contents("php://input"), true);
$id = $r['admin_id'] ?? 0;
$pdo->prepare("UPDATE tbladministrator SET Verified=1 WHERE AdminID=?")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"User approved"]);
