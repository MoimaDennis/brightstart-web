<?php
require 'db.php';
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM tblvolunteer WHERE FullName LIKE ? OR SubjectSpeciality LIKE ? OR ContactEmail LIKE ? ORDER BY VolunteerID DESC LIMIT 200");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM tblvolunteer ORDER BY VolunteerID DESC LIMIT 200");
}
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
