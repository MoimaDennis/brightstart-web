<?php
require 'db.php';
$stmt = $pdo->query("
    SELECT s.*, p.ProgramName, v.FullName AS VolName
    FROM tblsession s
    JOIN tblprogram p ON s.ProgramID = p.ProgramID
    JOIN tblvolunteer v ON s.VolunteerID = v.VolunteerID
    ORDER BY s.SessionDate DESC LIMIT 200
");
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
