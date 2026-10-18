<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblprogram (ProgramName,Description,StartDate,EndDate,Status) VALUES (?,?,?,?,?)");
$stmt->execute([$r['program_name'],$r['description'],$r['start_date'],$r['end_date'],$r['status']]);
echo json_encode(["success"=>true,"message"=>"Program added"]);
