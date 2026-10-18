<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg=''; $msgType='success';
$action=$_GET['action']??'list';
if ($currentRole !== 'Admin' && $action !== 'list') {
    header("Location: " . basename($_SERVER['PHP_SELF']));
    exit();
}
$editProg=null;

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $pn  = trim($_POST['program_name']);
    $desc= trim($_POST['description']);
    $sd  = trim($_POST['start_date']);
    $ed  = trim($_POST['end_date']);
    $st  = trim($_POST['status']);

    if ($_POST['action']==='add') {
        $stmt=$conn->prepare(
            "INSERT INTO tblProgram (ProgramName,Description,StartDate,EndDate,Status)
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("sssss",$pn,$desc,$sd,$ed,$st);
        if ($stmt->execute()) { $msg="Program '$pn' created."; }
        else { $msg='Error: '.$stmt->error; $msgType='danger'; }
        $stmt->close();
    } elseif ($_POST['action']==='update') {
        $id=(int)$_POST['prog_id'];
        $stmt=$conn->prepare(
            "UPDATE tblProgram
             SET ProgramName=?,Description=?,StartDate=?,EndDate=?,Status=?
             WHERE ProgramID=?"
        );
        $stmt->bind_param("sssssi",$pn,$desc,$sd,$ed,$st,$id);
        if ($stmt->execute()) { $msg="Program updated."; }
        else { $msg='Error: '.$stmt->error; $msgType='danger'; }
        $stmt->close();
    }
    $action='list';
}

if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $stmt=$conn->prepare("DELETE FROM tblProgram WHERE ProgramID=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) { $msg='Program deleted.'; }
    else { $msg='Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
    $action='list';
}

if ($action==='edit' && isset($_GET['id'])) {
    $res=$conn->query(
        "SELECT * FROM tblProgram WHERE ProgramID=".(int)$_GET['id']
    );
    $editProg=$res->fetch_assoc();
}

$programs=$conn->query(
    "SELECT * FROM tblProgram ORDER BY StartDate DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs – Bright Start</title>
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
        <h2><?= $action==='edit' ? '✏️ Edit Program' : '➕ Create Program' ?></h2>
        <form method="POST" action="programs.php">
            <input type="hidden" name="action"
                   value="<?= $action==='edit'?'update':'add' ?>">
            <?php if ($editProg): ?>
                <input type="hidden" name="prog_id"
                       value="<?= $editProg['ProgramID'] ?>">
            <?php endif; ?>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Program Name *</label>
                    <input type="text" name="program_name" required
                           value="<?= htmlspecialchars($editProg['ProgramName']??'') ?>"
                           placeholder="Maths Literacy Program">
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status">
                        <?php foreach (['Active','Pending','Closed'] as $s): ?>
                            <option value="<?=$s?>"
                                <?= ($editProg['Status']??'')===$s?'selected':'' ?>>
                                <?=$s?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" required
                           value="<?= $editProg['StartDate']??'' ?>">
                </div>
                <div class="form-group">
                    <label>End Date *</label>
                    <input type="date" name="end_date" required
                           value="<?= $editProg['EndDate']??'' ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"
                          placeholder="Brief description..."><?=
                    htmlspecialchars($editProg['Description']??'')
                ?></textarea>
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action==='edit' ? '💾 Update' : '✅ Create Program' ?>
                </button>
                <a href="programs.php" class="btn btn-warning">Cancel</a>
            </div>
        </form>
    </div>

    <?php else: ?>
    <div class="card">
        <h2>📚 Educational Programs</h2>
        <div style="margin-bottom:16px; display:flex; gap:10px;">
            <a href="programs.php?action=add" class="btn btn-accent">
                + Create Program
            </a>
            <a href="sessions.php" class="btn btn-primary">
                🗓️ Manage Sessions
            </a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Program Name</th><th>Description</th>
                    <th>Start</th><th>End</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($programs && $programs->num_rows>0):
                while ($p=$programs->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['ProgramID'] ?></td>
                    <td><strong><?= htmlspecialchars($p['ProgramName']) ?></strong></td>
                    <td style="max-width:180px; overflow:hidden;
                                text-overflow:ellipsis; white-space:nowrap;">
                        <?= htmlspecialchars($p['Description']) ?>
                    </td>
                    <td><?= date('d M Y',strtotime($p['StartDate'])) ?></td>
                    <td><?= date('d M Y',strtotime($p['EndDate'])) ?></td>
                    <td>
                        <span class="badge-<?= strtolower($p['Status']) ?>">
                            <?= $p['Status'] ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="programs.php?action=edit&id=<?= $p['ProgramID'] ?>"
                           class="btn btn-warning btn-sm">✏️</a>
                        <a href="enrolments.php?prog_id=<?= $p['ProgramID'] ?>"
                           class="btn btn-primary btn-sm">👥 Enrol</a>
                        <a href="programs.php?delete=<?= $p['ProgramID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="7"
                        style="text-align:center;padding:24px;color:#999;">
                        No programs yet.
                        <a href="programs.php?action=add">Create one →</a>
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