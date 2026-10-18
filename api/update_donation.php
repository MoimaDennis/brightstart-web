<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$r  = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("UPDATE tbldonation SET DonorName=?,DonorContact=?,DonationAmount=?,DonationDate=?,DonationType=?,Notes=? WHERE DonationID=?");
$stmt->execute([$r['donor_name'],$r['donor_contact'],$r['donation_amount'],$r['donation_date'],$r['donation_type'],$r['notes'],$id]);
echo json_encode(["success"=>true,"message"=>"Donation updated"]);
