<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg=''; $msgType='success';
$sessionId=(int)($_GET['session_id']??0);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['attendance'])) {
    $sid=(int)$_POST['session_id'];
    foreach ($_POST['attendance'] as $studentId=>$status) {
        $studentId=(int)$studentId;
        $notes=trim($_POST['notes'][$studentId]??'');
        $stmt=$conn->prepare(
            "INSERT INTO tblAttendance
             (SessionID,StudentID,AttendanceStatus,Notes)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE
             AttendanceStatus=VALUES(AttendanceStatus),
             Notes=VALUES(Notes)"
        );
        $stmt->bind_param("iiss",$sid,$studentId,$status,$notes);
        $stmt->execute();
        $stmt->close();
    }
    $msg="Attendance saved successfully for session #$sid.";
}

$sessions=$conn->query(
    "SELECT s.SessionID,p.ProgramName,v.FullName AS VolName,
            s.SessionDate,s.Venue
     FROM tblSession s
     JOIN tblProgram p ON s.ProgramID=p.ProgramID
     JOIN tblVolunteer v ON s.VolunteerID=v.VolunteerID
     ORDER BY s.SessionDate DESC"
);

$sessionInfo=null;
$enrolledStudents=[];
if ($sessionId>0) {
    $res=$conn->query(
        "SELECT s.*,p.ProgramName,v.FullName AS VolName,s.ProgramID
         FROM tblSession s
         JOIN tblProgram p ON s.ProgramID=p.ProgramID
         JOIN tblVolunteer v ON s.VolunteerID=v.VolunteerID
         WHERE s.SessionID=$sessionId"
    );
    $sessionInfo=$res->fetch_assoc();
    if ($sessionInfo) {
        $progId=$sessionInfo['ProgramID'];
        $studRes=$conn->query(
            "SELECT st.StudentID,st.FirstName,st.LastName,
                    a.AttendanceStatus,a.Notes
             FROM tblEnrolment e
             JOIN tblStudent st ON e.StudentID=st.StudentID
             LEFT JOIN tblAttendance a
                ON a.StudentID=st.StudentID
                AND a.SessionID=$sessionId
             WHERE e.ProgramID=$progId AND e.Status='Active'
             ORDER BY st.LastName"
        );
        while ($r=$studRes->fetch_assoc()) {
            $enrolledStudents[]=$r;
        }
    }
}

$history=$conn->query(
    "SELECT a.AttendanceStatus,a.Notes,
            CONCAT(s.FirstName,' ',s.LastName) AS StudentName,
            se.SessionDate,p.ProgramName
     FROM tblAttendance a
     JOIN tblStudent s ON a.StudentID=s.StudentID
     JOIN tblSession se ON a.SessionID=se.SessionID
     JOIN tblProgram p ON se.ProgramID=p.ProgramID
     ORDER BY se.SessionDate DESC LIMIT 20"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance – Bright Start</title>
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
        <h2>📋 Log Session Attendance</h2>

        <form method="GET" action="attendance.php"
              style="margin-bottom:20px;">
            <div class="form-group">
                <label>Select Session</label>
                <select name="session_id"
                        onchange="this.form.submit()"
                        style="max-width:500px;">
                    <option value="">-- Select a Session --</option>
                    <?php
                    $sessions->data_seek(0);
                    while ($s=$sessions->fetch_assoc()): ?>
                        <option value="<?= $s['SessionID'] ?>"
                            <?= $sessionId==$s['SessionID']
                                ?'selected':'' ?>>
                            <?= date('d M Y',strtotime($s['SessionDate'])) ?>
                            – <?= htmlspecialchars($s['ProgramName']) ?>
                            (<?= htmlspecialchars($s['VolName']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <?php if ($sessionInfo): ?>
        <div style="background:#e8f0fb; border-radius:8px;
                    padding:12px 16px; margin-bottom:18px;
                    font-size:.9rem;">
            <strong>Program:</strong>
            <?= htmlspecialchars($sessionInfo['ProgramName']) ?> |
            <strong>Volunteer:</strong>
            <?= htmlspecialchars($sessionInfo['VolName']) ?> |
            <strong>Date:</strong>
            <?= date('d M Y',strtotime($sessionInfo['SessionDate'])) ?> |
            <strong>Venue:</strong>
            <?= htmlspecialchars($sessionInfo['Venue']) ?>
        </div>

        <?php if (empty($enrolledStudents)): ?>
            <div class="alert alert-warning">
                No students enrolled in this program.
                <a href="enrolments.php">Enrol students first →</a>
            </div>
        <?php else: ?>
        <form method="POST"
              action="attendance.php?session_id=<?= $sessionId ?>">
            <input type="hidden" name="session_id"
                   value="<?= $sessionId ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Attendance Status</th>
                        <th>Notes (optional)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($enrolledStudents as $st): ?>
                <tr>
                    <td>
                        <strong>
                            <?= htmlspecialchars(
                                $st['FirstName'].' '.$st['LastName']
                            ) ?>
                        </strong>
                    </td>
                    <td>
                        <select name="attendance[<?= $st['StudentID'] ?>]"
                                style="padding:6px 10px; border-radius:5px;
                                       border:1px solid #ccc;">
                            <?php
                            foreach (['Present','Absent','Excused'] as $opt):
                            ?>
                                <option value="<?= $opt ?>"
                                    <?= ($st['AttendanceStatus']??'')===$opt
                                        ?'selected':'' ?>>
                                    <?= $opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               name="notes[<?= $st['StudentID'] ?>]"
                               value="<?= htmlspecialchars(
                                   $st['Notes']??'') ?>"
                               placeholder="Optional..."
                               style="width:100%; padding:6px;
                                      border:1px solid #ccc;
                                      border-radius:5px;">
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:14px; display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">
                    ✅ Save Attendance
                </button>
                <a href="attendance.php" class="btn btn-warning">
                    Cancel
                </a>
            </div>
        </form>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>📊 Recent Attendance Records</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th><th>Program</th>
                    <th>Session Date</th><th>Status</th><th>Notes</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($history && $history->num_rows>0):
                while ($h=$history->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($h['StudentName']) ?></td>
                    <td><?= htmlspecialchars($h['ProgramName']) ?></td>
                    <td><?= date('d M Y',strtotime($h['SessionDate'])) ?></td>
                    <td>
                        <span class="badge-<?= strtolower($h['AttendanceStatus']) ?>">
                            <?= $h['AttendanceStatus'] ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($h['Notes']??'') ?></td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="5"
                        style="text-align:center;padding:20px;color:#999;">
                        No attendance records yet.
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