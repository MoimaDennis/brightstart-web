<?php
require 'db.php';
$stmt = $pdo->query("
    SELECT a.*, CONCAT(s.FirstName,' ',s.LastName) AS StudentName,
           p.ProgramName, se.SessionDate
    FROM tblattendance a
    JOIN tblstudent s ON a.StudentID = s.StudentID
    JOIN tblsession se ON a.SessionID = se.SessionID
    JOIN tblprogram p ON se.ProgramID = p.ProgramID
    ORDER BY a.AttendanceID DESC LIMIT 200
");
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
