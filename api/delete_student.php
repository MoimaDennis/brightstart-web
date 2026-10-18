<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM tblstudent WHERE StudentID=?")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"Student deleted"]);
