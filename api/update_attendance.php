<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblattendance SET SessionID=?,StudentID=?,AttendanceStatus=?,Notes=? WHERE AttendanceID=?");
$stmt->execute([$r['session_id'],$r['student_id'],$r['attendance_status'],$r['notes'],$id]);
echo json_encode(["success"=>true,"message"=>"Attendance updated"]);
