<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM tblperformance WHERE PerformanceID=?")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"Performance deleted"]);
