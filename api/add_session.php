<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblsession (ProgramID,VolunteerID,SessionDate,StartTime,EndTime,Venue) VALUES (?,?,?,?,?,?)");
$stmt->execute([$r['program_id'],$r['volunteer_id'],$r['session_date'],$r['start_time'],$r['end_time'],$r['venue']]);
echo json_encode(["success"=>true,"message"=>"Session added"]);
