<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblstudent SET FirstName=?,LastName=?,DateOfBirth=?,Gender=?,CommunityArea=?,ContactNumber=?,EnrolmentDate=? WHERE StudentID=?");
$stmt->execute([$r['first_name'],$r['last_name'],$r['date_of_birth'],$r['gender'],$r['community_area'],$r['contact_number'],$r['enrolment_date'],$id]);
echo json_encode(["success"=>true,"message"=>"Student updated"]);
