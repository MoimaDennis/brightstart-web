<?php
require 'db.php';
$r = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("INSERT INTO tbldonation (DonorName,DonorContact,DonationAmount,DonationDate,DonationType,Notes) VALUES (?,?,?,?,?,?)");
$stmt->execute([$r['donor_name'],$r['donor_contact'],$r['donation_amount'],$r['donation_date'],$r['donation_type'],$r['notes']]);
echo json_encode(["success"=>true,"message"=>"Donation added"]);
