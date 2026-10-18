<?php
require 'db.php';
$stmt = $pdo->query("SELECT AdminID,FullName,Email,Username,Role,Verified,CreatedAt FROM administrator ORDER BY AdminID DESC");
echo json_encode(["success"=>true,"data"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
