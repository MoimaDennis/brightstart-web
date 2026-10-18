<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblperformance SET StudentID=?,ProgramID=?,AssessmentDate=?,Score=?,Grade=?,Comments=? WHERE PerformanceID=?");
$stmt->execute([$r['student_id'],$r['program_id'],$r['assessment_date'],$r['score'],$r['grade'],$r['comments'],$id]);
echo json_encode(["success"=>true,"message"=>"Performance updated"]);
