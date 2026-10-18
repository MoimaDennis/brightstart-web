<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM tbldonation WHERE DonationID=?")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"Donation deleted"]);
