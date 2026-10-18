<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$loggedIn = isset($_SESSION['admin_id']);
if ($loggedIn) {
    require_once 'includes/auth_guard.php';
    require_once 'includes/DBConn.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Help Centre – Bright Start</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if ($loggedIn): include 'includes/nav.php'; else: ?>
<nav>
    <a class="logo" href="index.php">Bright<span>Start</span></a>
    <ul style="display:flex;"><li><a href="login.php">Sign In</a></li><li><a href="register.php">Register</a></li></ul>
</nav>
<?php endif; ?>

<div class="container">
    <h1 class="page-title">❓ Help <em>Centre</em></h1>
    <p class="page-subtitle">Quick answers and step-by-step guides for using the Bright Start website.</p>

    <div class="card help-list">
        <h2>🚀 Getting started</h2>
        <details open><summary>How do I create an account?</summary>
            <ol><li>Open the website and click <strong>Join Us</strong> (or <strong>Register New Account</strong> on the login page).</li>
                <li>Fill in your name, email, a username (4+ characters), your role and a password (8+ characters with letters and numbers).</li>
                <li>Click <strong>Create Account</strong>.</li>
                <li>Wait for an Administrator to approve your account. You can sign in once it is approved.</li></ol></details>
        <details><summary>How do I sign in?</summary>
            <ol><li>Go to the login page.</li><li>Type your email and password and click <strong>Login</strong>, <em>or</em> click <strong>Continue with Google</strong> and choose your Google account.</li></ol></details>
        <details><summary>I forgot my password. What now?</summary>
            <p>Ask an Administrator to help you. They can update your account on the <strong>Users</strong> page. You can also sign in with Google if your account uses your Google email.</p></details>
        <details><summary>Why does it say my account is pending?</summary>
            <p>New accounts must be approved by an Administrator for safety. Please wait for approval, then sign in again.</p></details>
    </div>

    <div class="card help-list">
        <h2>🧭 Using the system</h2>
        <details><summary>How do I register a student? (Admin)</summary>
            <ol><li>Open <strong>Students ▾ → Register New Student</strong>.</li><li>Fill in every field and click <strong>Register Student</strong>.</li></ol></details>
        <details><summary>How do I enrol a student in a program? (Admin)</summary>
            <ol><li>Open <strong>Students ▾ → Manage Enrolments</strong>.</li><li>Choose the student, the program and the status.</li><li>Click <strong>Enrol Student</strong>.</li></ol></details>
        <details><summary>How do I record attendance?</summary>
            <ol><li>Open <strong>Track ▾ → Log Attendance</strong>.</li><li>Select a session.</li><li>Mark each student Present, Absent or Excused and add notes if needed.</li><li>Click <strong>Save Attendance</strong>.</li></ol></details>
        <details><summary>How do I record a student's performance?</summary>
            <ol><li>Open <strong>Track ▾ → Record Performance</strong>.</li><li>Choose the student and program, enter the score (0–100) and a comment.</li><li>Click <strong>Save Performance Record</strong>. The grade (A–F) is calculated automatically.</li></ol></details>
        <details><summary>How do I make a donation?</summary>
            <ol><li>Click <strong>Donate</strong> in the menu.</li><li>Enter your details and the amount, then submit.</li></ol></details>
        <details><summary>How do I view reports? (Admin)</summary>
            <p>Open <strong>Reports</strong>, pick a report tab, choose filters and click the filter button.</p></details>
    </div>

    <div class="card help-list">
        <h2>⚙️ Settings and troubleshooting</h2>
        <details><summary>How do I change the theme or text size?</summary>
            <ol><li>Click <strong>⚙️ Settings</strong> in the bar at the top of the page.</li><li>Pick a theme, text size and whether to show the dashboard slider.</li><li>Click <strong>Save Settings</strong>.</li></ol></details>
        <details><summary>How do I change my password?</summary>
            <ol><li>Open <strong>⚙️ Settings</strong>.</li><li>In <strong>Change Password</strong> type your current password and the new one twice.</li><li>Click <strong>Change Password</strong>.</li></ol></details>
        <details><summary>The page shows an error message. What should I do?</summary>
            <p>Read the message in the red box: it tells you which field to fix. Check that all required fields are filled in and dates are valid. If the problem continues, contact us below.</p></details>
        <details><summary>The menu does not show on my phone.</summary>
            <p>Tap the <strong>☰</strong> button at the top right to open the menu, then tap a heading such as <strong>Students ▾</strong> to see its options.</p></details>
    </div>

    <div class="card help-list">
        <h2>👥 Who can do what?</h2>
        <div class="table-scroll"><table class="data-table">
            <thead><tr><th>Role</th><th>What they can do</th></tr></thead>
            <tbody>
                <tr><td><strong>Admin</strong></td><td>Everything: manage students, volunteers, programs, sessions, enrolments, attendance, performance, donations, reports and users.</td></tr>
                <tr><td><strong>Coordinator / Management</strong></td><td>View students, volunteers and programs; log attendance and performance; donate.</td></tr>
                <tr><td><strong>Volunteer</strong></td><td>View students, volunteers and programs; log attendance and performance; donate.</td></tr>
            </tbody>
        </table></div>
    </div>

    <div class="card">
        <h2>📬 Still stuck?</h2>
        <p>Email <a href="mailto:support@brightstart.org" style="color:var(--accent);">support@brightstart.org</a> and tell us which page you were on and what you clicked.</p>
        <p style="margin-top:12px;"><?= $loggedIn ? '<a href="dashboard.php" class="btn btn-primary btn-sm">← Back to Dashboard</a>' : '<a href="login.php" class="btn btn-primary btn-sm">← Back to Login</a>' ?></p>
    </div>
</div>
<footer><p>&copy; 2026 <span>Bright Start</span> Education Initiative</p></footer>
</body>
</html>