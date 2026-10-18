<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblvolunteer (FullName,ContactEmail,ContactNumber,SubjectSpeciality,JoinDate,Status) VALUES (?,?,?,?,?,?)");
$stmt->execute([$r['full_name'],$r['contact_email'],$r['contact_number'],$r['subject_speciality'],$r['join_date'],$r['status']]);
echo json_encode(["success"=>true,"message"=>"Volunteer added"]);
