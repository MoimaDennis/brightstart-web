<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblperformance (StudentID,ProgramID,AssessmentDate,Score,Grade,Comments) VALUES (?,?,?,?,?,?)");
$stmt->execute([$r['student_id'],$r['program_id'],$r['assessment_date'],$r['score'],$r['grade'],$r['comments']]);
echo json_encode(["success"=>true,"message"=>"Performance added"]);
