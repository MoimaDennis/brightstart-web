<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$report    = $_GET['report']     ?? '';
$stu_id    = (int)($_GET['student_id']  ?? 0);
$vol_id    = (int)($_GET['volunteer_id']?? 0);
$prog_id   = (int)($_GET['prog_id']     ?? 0);
$from      = trim($_GET['from']  ?? '');
$to        = trim($_GET['to']    ?? '');

function gradeLabel($s) {
    if ($s>=80) return 'A';
    if ($s>=70) return 'B';
    if ($s>=60) return 'C';
    if ($s>=50) return 'D';
    return 'F';
}

$students  = $conn->query(
    "SELECT StudentID,FirstName,LastName FROM tblStudent ORDER BY FirstName"
);
$volunteers= $conn->query(
    "SELECT VolunteerID,FullName FROM tblVolunteer ORDER BY FullName"
);
$programs  = $conn->query(
    "SELECT ProgramID,ProgramName FROM tblProgram ORDER BY ProgramName"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports – Bright Start</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-output { background:#fff; border:1px solid #ccc; border-radius:10px; padding:28px 32px; margin-top:20px; }
        .report-output h3 { color:#1a3c5e; border-bottom:2px solid #f0a500; padding-bottom:8px; margin-bottom:16px; margin-top:20px; }
        @media print { nav,.user-banner,.tabs,.card:first-child,.topbar,footer,.btn,form { display:none!important; } }
    </style>
</head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="container">

    <div class="card">
        <h2>📋 Reports Centre</h2>

        <!-- Report Tabs -->
        <div class="tabs">
            <a href="?report=progress"
               class="tab-link <?= $report==='progress'?'active':'' ?>">
                📊 Student Progress
            </a>
            <a href="?report=donation"
               class="tab-link <?= $report==='donation'?'active':'' ?>">
                💰 Donation Summary
            </a>
            <a href="?report=volunteer"
               class="tab-link <?= $report==='volunteer'?'active':'' ?>">
                🙋 Volunteer Activity
            </a>
            <a href="?report=enrolment"
               class="tab-link <?= $report==='enrolment'?'active':'' ?>">
                📋 Program Enrolment
            </a>
        </div>

        <?php if ($report === 'progress'): ?>
        <!-- STUDENT PROGRESS -->
        <form method="GET" action="reports.php"
              style="display:flex; gap:10px; flex-wrap:wrap;
                     align-items:flex-end; margin-bottom:16px;">
            <input type="hidden" name="report" value="progress">
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">Student</label>
                <select name="student_id">
                    <option value="">All Students</option>
                    <?php $students->data_seek(0);
                    while ($s=$students->fetch_assoc()): ?>
                        <option value="<?= $s['StudentID'] ?>"
                            <?= $stu_id==$s['StudentID']?'selected':'' ?>>
                            <?= htmlspecialchars($s['FirstName'].' '.$s['LastName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">From</label>
                <input type="date" name="from" value="<?= $from ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">To</label>
                <input type="date" name="to" value="<?= $to ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                📊 Generate
            </button>
            <button onclick="window.print()" type="button"
                    class="btn btn-sm" style="background:#eee;">
                🖨️ Print
            </button>
        </form>
        <?php
        $w='1=1';
        if ($stu_id) $w.=" AND p.StudentID=$stu_id";
        if ($from)   $w.=" AND p.AssessmentDate>='$from'";
        if ($to)     $w.=" AND p.AssessmentDate<='$to'";
        $perfRows=$conn->query(
            "SELECT p.*,
                    CONCAT(s.FirstName,' ',s.LastName) AS StudentName,
                    pr.ProgramName
             FROM tblPerformance p
             JOIN tblStudent s ON p.StudentID=s.StudentID
             JOIN tblProgram pr ON p.ProgramID=pr.ProgramID
             WHERE $w ORDER BY p.AssessmentDate DESC"
        );
        $avgRes=$conn->query(
            "SELECT AVG(Score) AS avg FROM tblPerformance p WHERE $w"
        );
        $avg=round($avgRes->fetch_assoc()['avg']??0,1);
        ?>
        <div class="report-output">
            <div style="display:flex; justify-content:space-between;
                        align-items:flex-start; margin-bottom:20px;">
                <div>
                    <strong style="font-size:1.2rem; color:#1a3c5e;">
                        Bright Start – Student Progress Report
                    </strong><br>
                    <span style="color:#888; font-size:.85rem;">
                        Generated: <?= date('d M Y H:i') ?>
                    </span>
                </div>
                <?php if ($avg > 0): ?>
                <div style="text-align:right;">
                    <div style="font-size:2rem; font-weight:700;
                                 color:#1a3c5e;">
                        <?= $avg ?>%
                    </div>
                    <div style="font-size:.82rem; color:#888;">
                        Average Score
                    </div>
                    <div class="grade-<?= gradeLabel($avg) ?>"
                         style="font-size:1.2rem;">
                        Grade <?= gradeLabel($avg) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <h3>📊 Performance Records</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th><th>Program</th>
                        <th>Date</th><th>Score</th>
                        <th>Grade</th><th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($perfRows && $perfRows->num_rows>0):
                    while ($p=$perfRows->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['StudentName']) ?></td>
                        <td><?= htmlspecialchars($p['ProgramName']) ?></td>
                        <td><?= date('d M Y',strtotime($p['AssessmentDate'])) ?></td>
                        <td><?= $p['Score'] ?>%</td>
                        <td>
                            <span class="grade-<?= $p['Grade'] ?>">
                                <?= $p['Grade'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($p['Comments']??'') ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6"
                            style="text-align:center;padding:16px;color:#999;">
                            No records found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($report === 'donation'): ?>
        <!-- DONATION SUMMARY -->
        <form method="GET" action="reports.php"
              style="display:flex; gap:10px; flex-wrap:wrap;
                     align-items:flex-end; margin-bottom:16px;">
            <input type="hidden" name="report" value="donation">
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">From</label>
                <input type="date" name="from" value="<?= $from ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">To</label>
                <input type="date" name="to" value="<?= $to ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                💰 Generate
            </button>
            <button onclick="window.print()" type="button"
                    class="btn btn-sm" style="background:#eee;">
                🖨️ Print
            </button>
        </form>
        <?php
        $dw='1=1';
        if ($from) $dw.=" AND DonationDate>='$from'";
        if ($to)   $dw.=" AND DonationDate<='$to'";
        $dRows=$conn->query(
            "SELECT * FROM tblDonation WHERE $dw ORDER BY DonationDate DESC"
        );
        $totals=$conn->query(
            "SELECT DonationType,SUM(DonationAmount) AS sub
             FROM tblDonation WHERE $dw GROUP BY DonationType"
        );
        $grandTotal=$conn->query(
            "SELECT SUM(DonationAmount) AS g FROM tblDonation WHERE $dw"
        )->fetch_assoc()['g']??0;
        ?>
        <div class="report-output">
            <div style="display:flex; justify-content:space-between;
                        margin-bottom:20px;">
                <div>
                    <strong style="font-size:1.2rem; color:#1a3c5e;">
                        Bright Start – Donation Summary Report
                    </strong><br>
                    <span style="color:#888; font-size:.85rem;">
                        Generated: <?= date('d M Y H:i') ?>
                    </span>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.8rem; font-weight:700;
                                 color:#2e7d32;">
                        R <?= number_format($grandTotal,2) ?>
                    </div>
                    <div style="font-size:.82rem; color:#888;">
                        Grand Total
                    </div>
                </div>
            </div>
            <h3>By Donation Type</h3>
            <table class="data-table" style="max-width:400px;">
                <thead>
                    <tr><th>Type</th><th>Total (R)</th></tr>
                </thead>
                <tbody>
                <?php while ($t=$totals->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($t['DonationType']) ?></td>
                    <td style="color:#2e7d32; font-weight:700;">
                        R <?= number_format($t['sub'],2) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <h3>All Donations</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Donor</th><th>Amount (R)</th>
                        <th>Date</th><th>Type</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $dRows->data_seek(0);
                while ($d=$dRows->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($d['DonorName']) ?></td>
                    <td style="color:#2e7d32; font-weight:700;">
                        R <?= number_format($d['DonationAmount'],2) ?>
                    </td>
                    <td><?= date('d M Y',strtotime($d['DonationDate'])) ?></td>
                    <td><?= htmlspecialchars($d['DonationType']) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($report === 'volunteer'): ?>
        <!-- VOLUNTEER ACTIVITY -->
        <form method="GET" action="reports.php"
              style="display:flex; gap:10px; flex-wrap:wrap;
                     align-items:flex-end; margin-bottom:16px;">
            <input type="hidden" name="report" value="volunteer">
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">Volunteer</label>
                <select name="volunteer_id">
                    <option value="">All Volunteers</option>
                    <?php $volunteers->data_seek(0);
                    while ($v=$volunteers->fetch_assoc()): ?>
                        <option value="<?= $v['VolunteerID'] ?>"
                            <?= $vol_id==$v['VolunteerID']?'selected':'' ?>>
                            <?= htmlspecialchars($v['FullName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                🙋 Generate
            </button>
            <button onclick="window.print()" type="button"
                    class="btn btn-sm" style="background:#eee;">
                🖨️ Print
            </button>
        </form>
        <?php
        $vw='1=1';
        if ($vol_id) $vw.=" AND s.VolunteerID=$vol_id";
        $volSess=$conn->query(
            "SELECT s.*,p.ProgramName,v.FullName AS VolName,
                    v.SubjectSpeciality
             FROM tblSession s
             JOIN tblProgram p ON s.ProgramID=p.ProgramID
             JOIN tblVolunteer v ON s.VolunteerID=v.VolunteerID
             WHERE $vw ORDER BY s.SessionDate DESC"
        );
        $totalSess=$conn->query(
            "SELECT COUNT(*) AS c FROM tblSession s WHERE $vw"
        )->fetch_assoc()['c']??0;
        ?>
        <div class="report-output">
            <div style="display:flex; justify-content:space-between;
                        margin-bottom:20px;">
                <div>
                    <strong style="font-size:1.2rem; color:#1a3c5e;">
                        Bright Start – Volunteer Activity Report
                    </strong><br>
                    <span style="color:#888; font-size:.85rem;">
                        Generated: <?= date('d M Y H:i') ?>
                    </span>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.8rem; font-weight:700;
                                 color:#1a3c5e;">
                        <?= $totalSess ?>
                    </div>
                    <div style="font-size:.82rem; color:#888;">
                        Sessions
                    </div>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Volunteer</th><th>Speciality</th>
                        <th>Program</th><th>Date</th><th>Venue</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $volSess->data_seek(0);
                if ($volSess->num_rows>0):
                    while ($vs=$volSess->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($vs['VolName']) ?></strong></td>
                        <td><?= htmlspecialchars($vs['SubjectSpeciality']) ?></td>
                        <td><?= htmlspecialchars($vs['ProgramName']) ?></td>
                        <td><?= date('d M Y',strtotime($vs['SessionDate'])) ?></td>
                        <td><?= htmlspecialchars($vs['Venue']) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5"
                            style="text-align:center;padding:16px;color:#999;">
                            No sessions found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($report === 'enrolment'): ?>
        <!-- ENROLMENT REPORT -->
        <form method="GET" action="reports.php"
              style="display:flex; gap:10px; flex-wrap:wrap;
                     align-items:flex-end; margin-bottom:16px;">
            <input type="hidden" name="report" value="enrolment">
            <div class="form-group" style="margin:0;">
                <label style="font-size:.82rem;">Program</label>
                <select name="prog_id">
                    <option value="">All Programs</option>
                    <?php $programs->data_seek(0);
                    while ($pr=$programs->fetch_assoc()): ?>
                        <option value="<?= $pr['ProgramID'] ?>"
                            <?= $prog_id==$pr['ProgramID']?'selected':'' ?>>
                            <?= htmlspecialchars($pr['ProgramName']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                📋 Generate
            </button>
            <button onclick="window.print()" type="button"
                    class="btn btn-sm" style="background:#eee;">
                🖨️ Print
            </button>
        </form>
        <?php
        $ew='1=1';
        if ($prog_id) $ew.=" AND e.ProgramID=$prog_id";
        $enrolRows=$conn->query(
            "SELECT e.*,
                    CONCAT(s.FirstName,' ',s.LastName) AS StudentName,
                    s.CommunityArea,p.ProgramName
             FROM tblEnrolment e
             JOIN tblStudent s ON e.StudentID=s.StudentID
             JOIN tblProgram p ON e.ProgramID=p.ProgramID
             WHERE $ew ORDER BY e.EnrolmentDate DESC"
        );
        $totalEnrol=$conn->query(
            "SELECT COUNT(*) AS c FROM tblEnrolment e WHERE $ew"
        )->fetch_assoc()['c']??0;
        ?>
        <div class="report-output">
            <div style="display:flex; justify-content:space-between;
                        margin-bottom:20px;">
                <div>
                    <strong style="font-size:1.2rem; color:#1a3c5e;">
                        Bright Start – Program Enrolment Report
                    </strong><br>
                    <span style="color:#888; font-size:.85rem;">
                        Generated: <?= date('d M Y H:i') ?>
                    </span>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.8rem; font-weight:700;
                                 color:#1a3c5e;">
                        <?= $totalEnrol ?>
                    </div>
                    <div style="font-size:.82rem; color:#888;">
                        Total Enrolments
                    </div>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th><th>Community</th>
                        <th>Program</th><th>Enrolled</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($enrolRows && $enrolRows->num_rows>0):
                    while ($er=$enrolRows->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($er['StudentName']) ?></td>
                        <td><?= htmlspecialchars($er['CommunityArea']) ?></td>
                        <td><?= htmlspecialchars($er['ProgramName']) ?></td>
                        <td><?= date('d M Y',strtotime($er['EnrolmentDate'])) ?></td>
                        <td>
                            <span class="badge-<?= strtolower($er['Status']) ?>">
                                <?= $er['Status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5"
                            style="text-align:center;padding:16px;color:#999;">
                            No enrolments found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <!-- LANDING -->
        <div style="display:grid; grid-template-columns:1fr 1fr;
                    gap:16px; margin-top:10px;">
            <a href="?report=progress" style="text-decoration:none;">
                <div class="stat-card green"
                     style="cursor:pointer;">
                    <div class="stat-icon">📊</div>
                    <div class="stat-num" style="font-size:1rem;">
                        Student Progress Report
                    </div>
                    <div class="stat-label">
                        Grades, scores, attendance summary
                    </div>
                </div>
            </a>
            <a href="?report=donation" style="text-decoration:none;">
                <div class="stat-card orange"
                     style="cursor:pointer;">
                    <div class="stat-icon">💰</div>
                    <div class="stat-num" style="font-size:1rem;">
                        Donation Summary Report
                    </div>
                    <div class="stat-label">
                        Totals by type, full donation list
                    </div>
                </div>
            </a>
            <a href="?report=volunteer" style="text-decoration:none;">
                <div class="stat-card"
                     style="cursor:pointer;">
                    <div class="stat-icon">🙋</div>
                    <div class="stat-num" style="font-size:1rem;">
                        Volunteer Activity Report
                    </div>
                    <div class="stat-label">
                        Sessions conducted per volunteer
                    </div>
                </div>
            </a>
            <a href="?report=enrolment" style="text-decoration:none;">
                <div class="stat-card gold"
                     style="cursor:pointer;">
                    <div class="stat-icon">📋</div>
                    <div class="stat-num" style="font-size:1rem;">
                        Program Enrolment Report
                    </div>
                    <div class="stat-label">
                        Students per program
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<footer>
    <p>&copy; 2026 <span>Bright Start</span> Education Initiative</p>
</footer>
</body>
</html>