<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';
if ($currentRole !== 'Admin') { header("Location: index.php"); exit(); }

$msg=''; $msgType='success';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $sid=(int)$_POST['student_id'];
    $pid=(int)$_POST['program_id'];
    $ed =trim($_POST['enrolment_date']);
    $st =trim($_POST['status']);

    $stmt=$conn->prepare(
        "INSERT INTO tblenrolment (StudentID,ProgramID,EnrolmentDate,Status)
         VALUES (?,?,?,?)"
    );
    $stmt->bind_param("iiss",$sid,$pid,$ed,$st);
    if ($stmt->execute()) { $msg="Student enrolled successfully."; }
    else { $msg='Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $stmt=$conn->prepare("DELETE FROM tblenrolment WHERE EnrolmentID=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) { $msg='Enrolment removed.'; }
    else { $msg='Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
}

$enrolments=$conn->query(
    "SELECT e.*,
            CONCAT(s.FirstName,' ',s.LastName) AS StudentName,
            p.ProgramName
     FROM tblenrolment e
     JOIN tblstudent s ON e.StudentID=s.StudentID
     JOIN tblprogram p ON e.ProgramID=p.ProgramID
     ORDER BY e.EnrolmentDate DESC"
);
$students=$conn->query(
    "SELECT StudentID,FirstName,LastName FROM tblstudent ORDER BY FirstName"
);
$programs=$conn->query(
    "SELECT ProgramID,ProgramName FROM tblprogram WHERE Status='Active'"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolments – Bright Start</title>
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
        <h2>📋 Enrol Student in Program</h2>
        <form method="POST" action="enrolments.php">
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Student *</label>
                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($s=$students->fetch_assoc()): ?>
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
                        <?php while ($p=$programs->fetch_assoc()): ?>
                            <option value="<?= $p['ProgramID'] ?>">
                                <?= htmlspecialchars($p['ProgramName']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Enrolment Date *</label>
                    <input type="date" name="enrolment_date"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status">
                    <option value="Active">Active</option>
                    <option value="Completed">Completed</option>
                    <option value="Withdrawn">Withdrawn</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                ✅ Enrol Student
            </button>
        </form>
    </div>

    <div class="card">
        <h2>📋 All Enrolments</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Student</th><th>Program</th>
                    <th>Enrolled</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($enrolments && $enrolments->num_rows>0):
                while ($e=$enrolments->fetch_assoc()): ?>
                <tr>
                    <td><?= $e['EnrolmentID'] ?></td>
                    <td><?= htmlspecialchars($e['StudentName']) ?></td>
                    <td><?= htmlspecialchars($e['ProgramName']) ?></td>
                    <td><?= date('d M Y',strtotime($e['EnrolmentDate'])) ?></td>
                    <td>
                        <span class="badge-<?= strtolower($e['Status']) ?>">
                            <?= $e['Status'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="enrolments.php?delete=<?= $e['EnrolmentID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Remove enrolment?')">
                            🗑️ Remove
                        </a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="6"
                        style="text-align:center;padding:24px;color:#999;">
                        No enrolments yet.
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