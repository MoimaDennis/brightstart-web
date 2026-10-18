<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblenrolment SET StudentID=?,ProgramID=?,EnrolmentDate=?,Status=? WHERE EnrolmentID=?");
$stmt->execute([$r['student_id'],$r['program_id'],$r['enrolment_date'],$r['status'],$id]);
echo json_encode(["success"=>true,"message"=>"Enrolment updated"]);
