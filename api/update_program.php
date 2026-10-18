<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblprogram SET ProgramName=?,Description=?,StartDate=?,EndDate=?,Status=? WHERE ProgramID=?");
$stmt->execute([$r['program_name'],$r['description'],$r['start_date'],$r['end_date'],$r['status'],$id]);
echo json_encode(["success"=>true,"message"=>"Program updated"]);
