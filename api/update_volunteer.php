<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tblvolunteer SET FullName=?,ContactEmail=?,ContactNumber=?,SubjectSpeciality=?,JoinDate=?,Status=? WHERE VolunteerID=?");
$stmt->execute([$r['full_name'],$r['contact_email'],$r['contact_number'],$r['subject_speciality'],$r['join_date'],$r['status'],$id]);
echo json_encode(["success"=>true,"message"=>"Volunteer updated"]);
