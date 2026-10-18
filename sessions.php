<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg=''; $msgType='success';
$action=$_GET['action']??'list';
$editSess=null;

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $pid=(int)$_POST['program_id'];
    $vid=(int)$_POST['volunteer_id'];
    $sd =trim($_POST['session_date']);
    $st =trim($_POST['start_time']);
    $et =trim($_POST['end_time']);
    $vn =trim($_POST['venue']);

    if ($_POST['action']==='add') {
        $stmt=$conn->prepare(
            "INSERT INTO tblSession
             (ProgramID,VolunteerID,SessionDate,StartTime,EndTime,Venue)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("iissss",$pid,$vid,$sd,$st,$et,$vn);
        if ($stmt->execute()) { $msg="Session scheduled for $sd."; }
        else { $msg='Error: '.$stmt->error; $msgType='danger'; }
        $stmt->close();
    } elseif ($_POST['action']==='update') {
        $id=(int)$_POST['sess_id'];
        $stmt=$conn->prepare(
            "UPDATE tblSession
             SET ProgramID=?,VolunteerID=?,SessionDate=?,
                 StartTime=?,EndTime=?,Venue=?
             WHERE SessionID=?"
        );
        $stmt->bind_param("iissssi",$pid,$vid,$sd,$st,$et,$vn,$id);
        if ($stmt->execute()) { $msg="Session updated."; }
        else { $msg='Error: '.$stmt->error; $msgType='danger'; }
        $stmt->close();
    }
    $action='list';
}

if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $stmt=$conn->prepare("DELETE FROM tblSession WHERE SessionID=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) { $msg='Session deleted.'; }
    else { $msg='Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
    $action='list';
}

if ($action==='edit' && isset($_GET['id'])) {
    $res=$conn->query(
        "SELECT * FROM tblSession WHERE SessionID=".(int)$_GET['id']
    );
    $editSess=$res->fetch_assoc();
}

$sessions=$conn->query(
    "SELECT s.*,p.ProgramName,v.FullName AS VolName
     FROM tblSession s
     JOIN tblProgram p ON s.ProgramID=p.ProgramID
     JOIN tblVolunteer v ON s.VolunteerID=v.VolunteerID
     ORDER BY s.SessionDate DESC"
);
$programs=$conn->query(
    "SELECT * FROM tblProgram WHERE Status='Active' ORDER BY ProgramName"
);
$volunteers=$conn->query(
    "SELECT * FROM tblVolunteer WHERE Status='Active' ORDER BY FullName"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions – Bright Start</title>
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

    <?php if ($action==='add' || $action==='edit'): ?>
    <div class="card">
        <h2><?= $action==='edit' ? '✏️ Edit Session' : '🗓️ Schedule Session' ?></h2>
        <form method="POST" action="sessions.php">
            <input type="hidden" name="action"
                   value="<?= $action==='edit'?'update':'add' ?>">
            <?php if ($editSess): ?>
                <input type="hidden" name="sess_id"
                       value="<?= $editSess['SessionID'] ?>">
            <?php endif; ?>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Program *</label>
                    <select name="program_id" required>
                        <option value="">-- Select Program --</option>
                        <?php
                        $programs->data_seek(0);
                        while ($p=$programs->fetch_assoc()): ?>
                            <option value="<?= $p['ProgramID'] ?>"
                                <?= ($editSess['ProgramID']??0)==$p['ProgramID']
                                    ?'selected':'' ?>>
                                <?= htmlspecialchars($p['ProgramName']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Volunteer *</label>
                    <select name="volunteer_id" required>
                        <option value="">-- Select Volunteer --</option>
                        <?php
                        $volunteers->data_seek(0);
                        while ($v=$volunteers->fetch_assoc()): ?>
                            <option value="<?= $v['VolunteerID'] ?>"
                                <?= ($editSess['VolunteerID']??0)==$v['VolunteerID']
                                    ?'selected':'' ?>>
                                <?= htmlspecialchars(
                                    $v['FullName'].' – '.$v['SubjectSpeciality']
                                ) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Session Date *</label>
                    <input type="date" name="session_date" required
                           value="<?= $editSess['SessionDate']??'' ?>">
                </div>
                <div class="form-group">
                    <label>Venue *</label>
                    <input type="text" name="venue" required
                           value="<?= htmlspecialchars($editSess['Venue']??'') ?>"
                           placeholder="Soweto Community Hall">
                </div>
                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="time" name="start_time" required
                           value="<?= $editSess['StartTime']??'' ?>">
                </div>
                <div class="form-group">
                    <label>End Time *</label>
                    <input type="time" name="end_time" required
                           value="<?= $editSess['EndTime']??'' ?>">
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action==='edit' ? '💾 Update' : '✅ Schedule Session' ?>
                </button>
                <a href="sessions.php" class="btn btn-warning">Cancel</a>
            </div>
        </form>
    </div>

    <?php else: ?>
    <div class="card">
        <h2>🗓️ Tutoring Sessions</h2>
        <div style="margin-bottom:16px;">
            <a href="sessions.php?action=add" class="btn btn-accent">
                + Schedule Session
            </a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Program</th><th>Volunteer</th>
                    <th>Date</th><th>Time</th><th>Venue</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($sessions && $sessions->num_rows>0):
                while ($s=$sessions->fetch_assoc()): ?>
                <tr>
                    <td><?= $s['SessionID'] ?></td>
                    <td><?= htmlspecialchars($s['ProgramName']) ?></td>
                    <td><?= htmlspecialchars($s['VolName']) ?></td>
                    <td><?= date('d M Y',strtotime($s['SessionDate'])) ?></td>
                    <td>
                        <?= substr($s['StartTime'],0,5) ?>
                        –
                        <?= substr($s['EndTime'],0,5) ?>
                    </td>
                    <td><?= htmlspecialchars($s['Venue']) ?></td>
                    <td style="white-space:nowrap;">
                        <a href="sessions.php?action=edit&id=<?= $s['SessionID'] ?>"
                           class="btn btn-warning btn-sm">✏️</a>
                        <a href="attendance.php?session_id=<?= $s['SessionID'] ?>"
                           class="btn btn-primary btn-sm">📋 Attend</a>
                        <a href="sessions.php?delete=<?= $s['SessionID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="7"
                        style="text-align:center;padding:24px;color:#999;">
                        No sessions yet.
                        <a href="sessions.php?action=add">Schedule one →</a>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<footer>
    <p>&copy; 2026 <span>Bright Start</span> Education Initiative</p>
</footer>
</body>
</html>