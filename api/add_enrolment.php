<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblenrolment (StudentID,ProgramID,EnrolmentDate,Status) VALUES (?,?,?,?)");
$stmt->execute([$r['student_id'],$r['program_id'],$r['enrolment_date'],$r['status']]);
echo json_encode(["success"=>true,"message"=>"Enrolment added"]);
