<?php
session_start();
require_once 'includes/DBConn.php';
if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit(); }
$error = ''; $stickyEmail = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $stickyEmail = $email;
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT AdminID,FullName,Email,PasswordHash,Role,Verified FROM Administrator WHERE Email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$user) {
            $error = 'No account found with that email address.';
        } elseif (!password_verify($password, $user['PasswordHash'])) {
            $error = 'Incorrect password. Please try again.';
        } elseif ((int)$user['Verified'] !== 1) {
            $error = 'Your account is <strong>pending Administrator verification</strong>. Please wait for approval.';
        } else {
            $_SESSION['admin_id']    = $user['AdminID'];
            $_SESSION['admin_name']  = $user['FullName'];
            $_SESSION['admin_email'] = $user['Email'];
            $_SESSION['admin_role']  = $user['Role'];
            header("Location: index.php"); exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – Bright Start</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-box">
        <div style="text-align:center;margin-bottom:18px;font-size:2.8rem;">🎓</div>
        <h1>Bright Start</h1>
        <p class="sub">Education Support System – Staff Login</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST" action="login.php" novalidate>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($stickyEmail) ?>" placeholder="you@brightstart.org" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Login →</button>
        </form>
        <div style="text-align:center;margin-top:18px;padding-top:16px;border-top:1px solid #eee;">
            <p style="font-size:.88rem;color:#666;margin-bottom:8px;">Don't have an account?</p>
            <a href="register.php" class="btn btn-accent" style="font-size:.88rem;">✏️ Register New Account</a>
        </div>
        <p style="text-align:center;margin-top:14px;font-size:.78rem;color:#aaa;">
            Admin: <code>admin@brightstart.org</code> / <code>password</code>
        </p>
    </div>
</div>
</body>
</html>