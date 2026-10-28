<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
session_destroy();
json_out(["success" => true, "message" => "Logged out"]);
