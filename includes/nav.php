<?php
$pendingNav = $conn->query("SELECT COUNT(*) AS c FROM Administrator WHERE Verified = 0");
$pendingNavCount = $pendingNav ? $pendingNav->fetch_assoc()['c'] : 0;
?>
<div class="topbar">
    <span>🎓 Bright Start Education Support System</span>
    <span>Logged in as: <strong><?= $currentUser ?></strong> | Role: <?= $currentRole ?></span>
</div>
<nav>
    <a class="logo" href="dashboard.php">Bright<span>Start</span></a>
    <ul>
        <li><a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])==='dashboard.php'?'class="active"':'' ?>>🏠 Dashboard</a></li>

        <?php if ($currentRole === 'Admin'): ?>

        <li class="dropdown">
            <button class="dropdown-btn"> Students ▾</button>
            <div class="dropdown-menu">
                <a href="students.php">View All Students</a>
                <a href="students.php?action=add">Register New Student</a>
                <a href="enrolments.php">Manage Enrolments</a>
            </div>
        </li>

        <li class="dropdown">
            <button class="dropdown-btn"> Volunteers ▾</button>
            <div class="dropdown-menu">
                <a href="volunteers.php">View All Volunteers</a>
                <a href="volunteers.php?action=add">Register Volunteer</a>
            </div>
        </li>

        <li class="dropdown">
            <button class="dropdown-btn"> Programs ▾</button>
            <div class="dropdown-menu">
                <a href="programs.php">View Programs</a>
                <a href="programs.php?action=add">Create Program</a>
                <a href="sessions.php">Schedule Session</a>
            </div>
        </li>

        <li class="dropdown">
            <button class="dropdown-btn"> Track ▾</button>
            <div class="dropdown-menu">
                <a href="attendance.php">Log Attendance</a>
                <a href="performance.php">Record Performance</a>
            </div>
        </li>

        <li><a href="donations.php" <?= basename($_SERVER['PHP_SELF'])==='donations.php'?'class="active"':'' ?>> Donation History</a></li>
        <li><a href="reports.php"   <?= basename($_SERVER['PHP_SELF'])==='reports.php'  ?'class="active"':'' ?>> Reports</a></li>

        <li>
            <a href="admin_users.php">
                ⚙️ Users
                <?php if ($pendingNavCount > 0): ?>
                    <span style="background:#e65100;color:#fff;border-radius:10px;padding:1px 7px;font-size:.72rem;margin-left:3px;font-weight:700;"><?= $pendingNavCount ?></span>
                <?php endif; ?>
            </a>
        </li>

        <?php else: ?>

        <li><a href="students.php" <?= basename($_SERVER['PHP_SELF'])==='students.php'?'class="active"':'' ?>> Students</a></li>
        <li><a href="volunteers.php" <?= basename($_SERVER['PHP_SELF'])==='volunteers.php'?'class="active"':'' ?>> Volunteers</a></li>
        <li><a href="programs.php" <?= basename($_SERVER['PHP_SELF'])==='programs.php'?'class="active"':'' ?>> Programs</a></li>

        <li class="dropdown">
            <button class="dropdown-btn"> Track ▾</button>
            <div class="dropdown-menu">
                <a href="attendance.php">Log Attendance</a>
                <a href="performance.php">Record Performance</a>
            </div>
        </li>

        <li><a href="donate.php" <?= basename($_SERVER['PHP_SELF'])==='donate.php'?'class="active"':'' ?>>💚 Donate</a></li>

        <?php endif; ?>

        <li><a href="logout.php" style="color:#fdd835;">Logout</a></li>
    </ul>
</nav>
<div class="user-banner">
    <span>👤 <strong><?= $currentUser ?></strong> <span class="badge"><?= $currentRole ?></span></span>
    <span><?= $currentEmail ?> | <a href="logout.php">Logout →</a></span>
</div>