<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblattendance (SessionID,StudentID,AttendanceStatus,Notes) VALUES (?,?,?,?)");
$stmt->execute([$r['session_id'],$r['student_id'],$r['attendance_status'],$r['notes']]);
echo json_encode(["success"=>true,"message"=>"Attendance recorded"]);
