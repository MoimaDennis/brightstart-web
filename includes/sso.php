<?php
// Verifies a Firebase (Google sign-in) ID token by asking Google's Identity Toolkit.
// Returns ['email' => ..., 'name' => ..., 'verified' => bool] or null when the token is invalid.
function verify_firebase_token($idToken) {
    $url = cfg('FIREBASE_LOOKUP_URL', 'https://identitytoolkit.googleapis.com/v1/accounts:lookup') . '?key=' . urlencode(FIREBASE_API_KEY);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['idToken' => $idToken]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code !== 200) return null;
    $json = json_decode($body, true);
    $u = $json['users'][0] ?? null;
    if (!$u || empty($u['email'])) return null;
    return ['email' => strtolower($u['email']), 'name' => $u['displayName'] ?? $u['email'], 'verified' => !empty($u['emailVerified'])];
}