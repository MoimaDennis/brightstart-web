<?php
require 'db.php';
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM tblstudent WHERE FirstName LIKE ? OR LastName LIKE ? OR CommunityArea LIKE ? ORDER BY StudentID DESC LIMIT 200");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM tblstudent ORDER BY StudentID DESC LIMIT 200");
}
echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
