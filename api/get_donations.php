<?php
require 'db.php';
$stmt = $pdo->query("SELECT * FROM tbldonation ORDER BY DonationID DESC LIMIT 200");
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
