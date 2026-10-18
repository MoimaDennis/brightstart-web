<?php
require 'db.php';
$r  = json_decode(file_get_contents("php://input"), true);
$id = $r['admin_id'] ?? 0;
$pdo->prepare("DELETE FROM administrator WHERE AdminID=? AND Role != 'Admin'")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"User rejected and removed"]);
