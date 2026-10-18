<?php
session_start();
if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit(); }
require_once 'includes/DBConn.php';
require_once 'includes/validation.php';
$error = ''; $success = '';
$stickyName = ''; $stickyEmail = ''; $stickyUsername = ''; $stickyRole = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email']     ?? '');
    $username = trim($_POST['username']  ?? '');
    $password = trim($_POST['password']  ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    $role     = trim($_POST['role']      ?? '');
    $stickyName=$fullName; $stickyEmail=$email; $stickyUsername=$username; $stickyRole=$role;
    if (empty($fullName)||empty($email)||empty($username)||empty($password)||empty($confirm)||empty($role)) {
        $error = 'All fields are required.';
    } elseif ($e = validate_full_name($fullName)) {
        $error = $e;
    } elseif ($e = validate_email($email)) {
        $error = $e;
    } elseif ($e = validate_username($username)) {
        $error = $e;
    } elseif (!in_array($role, ['Volunteer','Coordinator','Management'], true)) {
        $error = 'Please choose a valid role.';
    } elseif ($e = validate_password($password)) {
        $error = $e;
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $chk = $conn->prepare("SELECT AdminID FROM tbladministrator WHERE Email=? OR Username=?");
        $chk->bind_param("ss",$email,$username); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = 'That email or username is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO tbladministrator (FullName,Email,Username,PasswordHash,Role,Verified) VALUES (?,?,?,?,?,0)");
            $stmt->bind_param("sssss",$fullName,$email,$username,$hash,$role);
            if ($stmt->execute()) {
                $success = 'Registration successful! Your account is <strong>pending administrator verification</strong>. You will be able to login once an Admin approves your account.';
                $stickyName=$stickyEmail=$stickyUsername=$stickyRole='';
            } else {
                $error = 'Registration failed: ' . $stmt->error;
            }
            $stmt->close();
        }
        $chk->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – Bright Start</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper" style="align-items:flex-start;padding:40px 20px;">
    <div class="auth-box" style="max-width:500px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="font-size:3rem;">🎓</div>
            <h1 style="font-size:1.6rem;color:#1a3c5e;margin-bottom:4px;">Bright Start</h1>
            <p class="sub">Create your staff account</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <div style="text-align:center;margin-top:20px;"><a href="login.php" class="btn btn-primary">→ Go to Login</a></div>
        <?php else: ?>
        <form method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($stickyName) ?>" placeholder="Thabo Nkosi" required maxlength="100">
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($stickyEmail) ?>" placeholder="you@brightstart.org" required maxlength="100">
            </div>
            <div class="form-group">
                <label>Username * <span style="color:#999;font-size:.8rem;">(min 4 characters)</span></label>
                <input type="text" name="username" value="<?= htmlspecialchars($stickyUsername) ?>" placeholder="thabo123" required maxlength="50">
            </div>
            <div class="form-group">
                <label>Your Role *</label>
                <select name="role" required>
                    <option value="">-- Select your role --</option>
                    <?php foreach (['Volunteer','Coordinator','Management'] as $r): ?>
                        <option value="<?=$r?>" <?= $stickyRole===$r?'selected':'' ?>><?=$r?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#999;font-size:.78rem;">Admin role is assigned by the Administrator only.</small>
            </div>
            <div class="form-group">
                <label>Password * <span style="color:#999;font-size:.8rem;">(min 8 characters, letters and numbers)</span></label>
                <input type="password" name="password" placeholder="Strong password" required maxlength="100">
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="Repeat your password" required maxlength="100">
            </div>
            <div class="alert alert-warning" style="font-size:.85rem;">
                ⚠️ After registering your account will be <strong>pending verification</strong> by an Administrator before you can login.
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">✅ Create Account</button>
        </form>
        <?php endif; ?>
        <p style="text-align:center;margin-top:18px;font-size:.88rem;color:#666;">
            Already have an account? <a href="login.php" style="color:#1a3c5e;font-weight:600;">Login here →</a>
        </p>
    </div>
</div>
</body>
</html>