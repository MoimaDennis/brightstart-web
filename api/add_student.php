<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tblstudent (FirstName,LastName,DateOfBirth,Gender,CommunityArea,ContactNumber,EnrolmentDate) VALUES (?,?,?,?,?,?,?)");
$stmt->execute([$r['first_name'],$r['last_name'],$r['date_of_birth'],$r['gender'],$r['community_area'],$r['contact_number'],$r['enrolment_date']]);
echo json_encode(["success"=>true,"message"=>"Student added"]);
