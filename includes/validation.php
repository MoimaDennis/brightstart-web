<?php
// Simple validation helpers (each returns an error message, or '' when the value is valid).
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? '' : 'Please enter a valid email address.';
}
function validate_username($username) {
    if (strlen($username) < 4)  return 'Username must be at least 4 characters.';
    if (strlen($username) > 50) return 'Username must be 50 characters or less.';
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $username)) return 'Username can only contain letters, numbers, dots, dashes and underscores.';
    return '';
}
function validate_password($password) {
    if (strlen($password) < 8)              return 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Za-z]/', $password)) return 'Password must contain at least one letter.';
    if (!preg_match('/[0-9]/', $password))    return 'Password must contain at least one number.';
    return '';
}
function validate_full_name($name) {
    $name = trim($name);
    if ($name === '' || strlen($name) < 2) return 'Please enter your full name.';
    if (strlen($name) > 100)               return 'Full name must be 100 characters or less.';
    return '';
}
function validate_setting($key, $value) {
    $allowed = ['theme' => ['dark', 'light'], 'text_size' => ['normal', 'large'], 'show_slider' => [0, 1, '0', '1', true, false]];
    return isset($allowed[$key]) && in_array($value, $allowed[$key], true);
}