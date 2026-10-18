<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg=''; $msgType='success';

function grade($s) {
    if ($s>=80) return 'A';
    if ($s>=70) return 'B';
    if ($s>=60) return 'C';
    if ($s>=50) return 'D';
    return 'F';
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])
    && $_POST['action']==='add') {
    $sid  =(int)$_POST['student_id'];
    $pid  =(int)$_POST['program_id'];
    $ad   =trim($_POST['assessment_date']);
    $score=(float)$_POST['score'];
    $g    =grade($score);
    $comm =trim($_POST['comments']);

    $stmt=$conn->prepare(
        "INSERT INTO tblperformance
         (StudentID,ProgramID,AssessmentDate,Score,Grade,Comments)
         VALUES (?,?,?,?,?,?)"
    );
    $stmt->bind_param("iisdss",$sid,$pid,$ad,$score,$g,$comm);
    if ($stmt->execute()) {
        $msg="Performance saved. Grade: $g";
    } else {
        $msg='Error: '.$stmt->error; $msgType='danger';
    }
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $stmt=$conn->prepare(
        "DELETE FROM tblperformance WHERE PerformanceID=?"
    );
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) { $msg='Record deleted.'; }
    else { $msg='Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
}

$filterStudent=(int)($_GET['student_id']??0);
$where=$filterStudent>0 ? "WHERE p.StudentID=$filterStudent" : '';
$performance=$conn->query(
    "SELECT p.*,
            CONCAT(s.FirstName,' ',s.LastName) AS StudentName,
            pr.ProgramName
     FROM tblperformance p
     JOIN tblstudent s ON p.StudentID=s.StudentID
     JOIN tblprogram pr ON p.ProgramID=pr.ProgramID
     $where
     ORDER BY p.AssessmentDate DESC"
);
$students=$conn->query(
    "SELECT StudentID,FirstName,LastName FROM tblstudent ORDER BY FirstName"
);
$programs=$conn->query(
    "SELECT ProgramID,ProgramName FROM tblprogram WHERE Status='Active'"
);
$avgScore=null;
if ($filterStudent>0) {
    $avgRes=$conn->query(
        "SELECT AVG(Score) AS avg FROM tblperformance
         WHERE StudentID=$filterStudent"
    );
    $avgScore=round($avgRes->fetch_assoc()['avg']??0,1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance – Bright Start</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="container">

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>📊 Record Student Performance</h2>
        <form method="POST" action="performance.php">
            <input type="hidden" name="action" value="add">
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Student *</label>
                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php
                        $students->data_seek(0);
                        while ($s=$students->fetch_assoc()): ?>
                            <option value="<?= $s['StudentID'] ?>">
                                <?= htmlspecialchars(
                                    $s['FirstName'].' '.$s['LastName']
                                ) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Program *</label>
                    <select name="program_id" required>
                        <option value="">-- Select Program --</option>
                        <?php
                        $programs->data_seek(0);
                        while ($p=$programs->fetch_assoc()): ?>
                            <option value="<?= $p['ProgramID'] ?>">
                                <?= htmlspecialchars($p['ProgramName']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Assessment Date *</label>
                    <input type="date" name="assessment_date"
                           required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Score (0–100) *</label>
                    <input type="number" name="score"
                           min="0" max="100" step="0.5"
                           required placeholder="75.5">
                </div>
                <div class="form-group">
                    <label>Auto Grade</label>
                    <input type="text" readonly
                           value="≥80=A  ≥70=B  ≥60=C  ≥50=D  <50=F"
                           style="background:#f5f5f5; color:#888;
                                  font-size:.8rem;">
                </div>
            </div>
            <div class="form-group">
                <label>Comments (optional)</label>
                <textarea name="comments" rows="2"
                          placeholder="Observations..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                ✅ Save Performance Record
            </button>
        </form>
    </div>

    <div class="card">
        <h2>📋 Performance History</h2>

        <form method="GET" action="performance.php"
              style="margin-bottom:16px; display:flex;
                     gap:10px; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1;">
                <label>Filter by Student</label>
                <select name="student_id"
                        onchange="this.form.submit()">
                    <option value="">-- All Students --</option>
                    <?php
                    $students->data_seek(0);
                    while ($s=$students->fetch_assoc()): ?>
                        <option value="<?= $s['StudentID'] ?>"
                            <?= $filterStudent==$s['StudentID']
                                ?'selected':'' ?>>
                            <?= htmlspecialchars(
                                $s['FirstName'].' '.$s['LastName']
                            ) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <a href="performance.php"
               class="btn btn-sm" style="background:#eee;">
                Clear
            </a>
        </form>

        <?php if ($avgScore !== null): ?>
            <div class="alert alert-info">
                📊 Average Score:
                <strong><?= $avgScore ?>%</strong> –
                Grade:
                <strong class="grade-<?= grade($avgScore) ?>">
                    <?= grade($avgScore) ?>
                </strong>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th><th>Program</th><th>Date</th>
                    <th>Score</th><th>Grade</th><th>Comments</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($performance && $performance->num_rows>0):
                while ($p=$performance->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['StudentName']) ?></strong></td>
                    <td><?= htmlspecialchars($p['ProgramName']) ?></td>
                    <td><?= date('d M Y',strtotime($p['AssessmentDate'])) ?></td>
                    <td><?= $p['Score'] ?>%</td>
                    <td>
                        <span class="grade-<?= $p['Grade'] ?>">
                            <?= $p['Grade'] ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($p['Comments']??'') ?></td>
                    <td>
                        <a href="performance.php?delete=<?= $p['PerformanceID'] ?><?= $filterStudent?"&student_id=$filterStudent":'' ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete?')">
                            🗑️
                        </a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="7"
                        style="text-align:center;padding:20px;color:#999;">
                        No performance records yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<footer>
    <p>&copy; 2026 <span>Bright Start</span> Education Initiative</p>
</footer>
</body>
</html>