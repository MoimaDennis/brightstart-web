<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM tblprogram WHERE ProgramID=?")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"Program deleted"]);
