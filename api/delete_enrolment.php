<?php
require 'db.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM tblenrolment WHERE EnrolmentID=?")->execute([$id]);
echo json_encode(["success"=>true,"message"=>"Enrolment deleted"]);
