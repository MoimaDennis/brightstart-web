<?php
require 'db.php';
$students   = $pdo->query("SELECT COUNT(*) FROM tblstudent")->fetchColumn();
$volunteers = $pdo->query("SELECT COUNT(*) FROM tblvolunteer")->fetchColumn();
$programs   = $pdo->query("SELECT COUNT(*) FROM tblprogram")->fetchColumn();
$sessions   = $pdo->query("SELECT COUNT(*) FROM tblsession")->fetchColumn();
$enrolments = $pdo->query("SELECT COUNT(*) FROM tblenrolment")->fetchColumn();
$donations  = $pdo->query("SELECT COUNT(*) FROM tbldonation")->fetchColumn();
$totalAmount = $pdo->query("SELECT COALESCE(SUM(DonationAmount),0) FROM tbldonation")->fetchColumn();

$recentStudents = $pdo->query("SELECT * FROM tblstudent ORDER BY StudentID DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recentDonations = $pdo->query("SELECT * FROM tbldonation ORDER BY DonationID DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$upcomingSessions = $pdo->query("
    SELECT s.*, p.ProgramName, CONCAT(v.FullName) AS VolName
    FROM tblsession s
    JOIN tblprogram p ON s.ProgramID = p.ProgramID
    JOIN tblvolunteer v ON s.VolunteerID = v.VolunteerID
    WHERE s.SessionDate >= CURDATE()
    ORDER BY s.SessionDate ASC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "students" => (int)$students, "volunteers" => (int)$volunteers,
    "programs" => (int)$programs, "sessions" => (int)$sessions,
    "enrolments" => (int)$enrolments, "donations" => (int)$donations,
    "total_donations_amount" => (float)$totalAmount,
    "recent_students" => $recentStudents,
    "recent_donations" => $recentDonations,
    "upcoming_sessions" => $upcomingSessions
]);
