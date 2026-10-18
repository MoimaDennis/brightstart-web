<?php
require 'db.php';
$stmt = $pdo->query("
    SELECT e.*, CONCAT(s.FirstName,' ',s.LastName) AS StudentName, p.ProgramName
    FROM tblenrolment e
    JOIN tblstudent s ON e.StudentID = s.StudentID
    JOIN tblprogram p ON e.ProgramID = p.ProgramID
    ORDER BY e.EnrolmentID DESC LIMIT 200
");
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
