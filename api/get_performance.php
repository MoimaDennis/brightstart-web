<?php
require 'db.php';
$stmt = $pdo->query("
    SELECT p.*, CONCAT(s.FirstName,' ',s.LastName) AS StudentName, pr.ProgramName
    FROM tblperformance p
    JOIN tblstudent s ON p.StudentID = s.StudentID
    JOIN tblprogram pr ON p.ProgramID = pr.ProgramID
    ORDER BY p.PerformanceID DESC LIMIT 200
");
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
