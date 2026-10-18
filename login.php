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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT AdminID,FullName,Email,PasswordHash,Role,Verified FROM tbladministrator WHERE Email=? LIMIT 1");
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
            session_regenerate_id(true);
            $_SESSION['admin_id']    = $user['AdminID'];
            $_SESSION['admin_name']  = $user['FullName'];
            $_SESSION['admin_email'] = $user['Email'];
            $_SESSION['admin_role']  = $user['Role'];
            header("Location: dashboard.php"); exit();
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
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($stickyEmail) ?>" placeholder="you@brightstart.org" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Login →</button>
        </form>

        <div class="divider"><span>or</span></div>
        <button type="button" id="googleBtn" class="btn btn-google btn-full">
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.5l6.7-6.7C35.6 2.4 30.2 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.8 6.1C12.3 13.6 17.7 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 7.1-10.1 7.1-17.5z"/><path fill="#FBBC05" d="M10.4 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.9-4.7l-7.8-6.1A24 24 0 0 0 0 24c0 3.9.9 7.5 2.6 10.8l7.8-6.1z"/><path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.5-5.8c-2 1.4-4.7 2.3-7.7 2.3-6.3 0-11.7-4.1-13.6-9.8l-7.8 6.1C6.5 42.6 14.6 48 24 48z"/></svg>
            Continue with Google
        </button>
        <div id="ssoMsg" class="alert" style="display:none;margin-top:14px;"></div>

        <div style="text-align:center;margin-top:18px;padding-top:16px;border-top:1px solid rgba(184,224,74,0.15);">
            <p style="font-size:.88rem;color:var(--text-light);margin-bottom:8px;">Don't have an account?</p>
            <a href="register.php" class="btn btn-accent" style="font-size:.88rem;">✏️ Register New Account</a>
            <p style="margin-top:14px;font-size:.85rem;"><a href="help.php" style="color:var(--accent);">❓ Need help signing in?</a></p>
        </div>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-auth-compat.js"></script>
<script src="js/firebase-config.js"></script>
<script src="js/sso.js"></script>
</body>
</html>