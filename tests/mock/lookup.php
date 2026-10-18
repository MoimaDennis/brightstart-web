<?php
// Fake Google server for tests. Token "valid:someone@example.com" is accepted, anything else is rejected.
header('Content-Type: application/json');
$in = json_decode(file_get_contents('php://input'), true);
$token = $in['idToken'] ?? '';
if (strpos($token, 'valid:') === 0) {
    $email = substr($token, 6);
    echo json_encode(['users' => [['email' => $email, 'displayName' => 'Test ' . $email, 'emailVerified' => true]]]);
} else {
    http_response_code(400);
    echo json_encode(['error' => ['message' => 'INVALID_ID_TOKEN']]);
}