<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblsession SET ProgramID=?,VolunteerID=?,SessionDate=?,StartTime=?,EndTime=?,Venue=? WHERE SessionID=?");
$stmt->execute([$r['program_id'],$r['volunteer_id'],$r['session_date'],$r['start_time'],$r['end_time'],$r['venue'],$id]);
echo json_encode(["success"=>true,"message"=>"Session updated"]);
