<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg = ''; $msgType = 'success';
$action = $_GET['action'] ?? 'list';
if ($currentRole !== 'Admin' && $action !== 'list') {
    header("Location: " . basename($_SERVER['PHP_SELF']));
    exit();
}
$editVol = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $fn  = trim($_POST['full_name']);
    $em  = trim($_POST['contact_email']);
    $cn  = trim($_POST['contact_number']);
    $sub = trim($_POST['subject_speciality']);
    $jd  = trim($_POST['join_date']);
    $st  = trim($_POST['status']);

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare(
            "INSERT INTO tblvolunteer
             (FullName,ContactEmail,ContactNumber,SubjectSpeciality,JoinDate,Status)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssssss",$fn,$em,$cn,$sub,$jd,$st);
        if ($stmt->execute()) {
            $msg = "Volunteer '$fn' registered successfully.";
        } else {
            $msg = 'Error: '.$stmt->error; $msgType='danger';
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'update') {
        $id = (int)$_POST['vol_id'];
        $stmt = $conn->prepare(
            "UPDATE tblvolunteer
             SET FullName=?,ContactEmail=?,ContactNumber=?,
                 SubjectSpeciality=?,JoinDate=?,Status=?
             WHERE VolunteerID=?"
        );
        $stmt->bind_param("ssssssi",$fn,$em,$cn,$sub,$jd,$st,$id);
        if ($stmt->execute()) {
            $msg = "Volunteer updated successfully.";
        } else {
            $msg = 'Error: '.$stmt->error; $msgType='danger';
        }
        $stmt->close();
    }
    $action = 'list';
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tblvolunteer WHERE VolunteerID=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) { $msg = 'Volunteer deleted.'; }
    else { $msg = 'Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
    $action = 'list';
}

if ($action === 'edit' && isset($_GET['id'])) {
    $res = $conn->query(
        "SELECT * FROM tblvolunteer WHERE VolunteerID=".(int)$_GET['id']
    );
    $editVol = $res->fetch_assoc();
}

$volunteers = $conn->query(
    "SELECT * FROM tblvolunteer ORDER BY FullName ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers – Bright Start</title>
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

    <?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <h2><?= $action==='edit' ? '✏️ Edit Volunteer' : '➕ Register Volunteer' ?></h2>
        <form method="POST" action="volunteers.php">
            <input type="hidden" name="action"
                   value="<?= $action==='edit' ? 'update' : 'add' ?>">
            <?php if ($editVol): ?>
                <input type="hidden" name="vol_id"
                       value="<?= $editVol['VolunteerID'] ?>">
            <?php endif; ?>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required
                           value="<?= htmlspecialchars($editVol['FullName']??'') ?>"
                           placeholder="Nomsa Dlamini">
                </div>
                <div class="form-group">
                    <label>Contact Email *</label>
                    <input type="email" name="contact_email" required
                           value="<?= htmlspecialchars($editVol['ContactEmail']??'') ?>"
                           placeholder="nomsa@example.co.za">
                </div>
                <div class="form-group">
                    <label>Contact Number *</label>
                    <input type="text" name="contact_number" required
                           value="<?= htmlspecialchars($editVol['ContactNumber']??'') ?>"
                           placeholder="0823456789">
                </div>
                <div class="form-group">
                    <label>Subject Speciality *</label>
                    <input type="text" name="subject_speciality" required
                           value="<?= htmlspecialchars($editVol['SubjectSpeciality']??'') ?>"
                           placeholder="Mathematics">
                </div>
                <div class="form-group">
                    <label>Join Date *</label>
                    <input type="date" name="join_date" required
                           value="<?= $editVol['JoinDate']??date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status">
                        <option value="Active"
                            <?= ($editVol['Status']??'')==='Active'?'selected':'' ?>>
                            Active
                        </option>
                        <option value="Inactive"
                            <?= ($editVol['Status']??'')==='Inactive'?'selected':'' ?>>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action==='edit' ? '💾 Update' : '✅ Register Volunteer' ?>
                </button>
                <a href="volunteers.php" class="btn btn-warning">Cancel</a>
            </div>
        </form>
    </div>

    <?php else: ?>
    <div class="card">
        <h2>🙋 Volunteer Register</h2>
        <div style="margin-bottom:16px;">
            <a href="volunteers.php?action=add" class="btn btn-accent">
                + Register Volunteer
            </a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Full Name</th><th>Email</th>
                    <th>Contact</th><th>Speciality</th>
                    <th>Joined</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($volunteers && $volunteers->num_rows > 0):
                while ($v = $volunteers->fetch_assoc()): ?>
                <tr>
                    <td><?= $v['VolunteerID'] ?></td>
                    <td><strong><?= htmlspecialchars($v['FullName']) ?></strong></td>
                    <td><?= htmlspecialchars($v['ContactEmail']) ?></td>
                    <td><?= htmlspecialchars($v['ContactNumber']) ?></td>
                    <td><?= htmlspecialchars($v['SubjectSpeciality']) ?></td>
                    <td><?= date('d M Y',strtotime($v['JoinDate'])) ?></td>
                    <td>
                        <span class="badge-<?= strtolower($v['Status']) ?>">
                            <?= $v['Status'] ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="volunteers.php?action=edit&id=<?= $v['VolunteerID'] ?>"
                           class="btn btn-warning btn-sm">✏️ Edit</a>
                        <a href="volunteers.php?delete=<?= $v['VolunteerID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this volunteer?')">
                            🗑️
                        </a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="8"
                        style="text-align:center;padding:24px;color:#999;">
                        No volunteers yet.
                        <a href="volunteers.php?action=add">Add one →</a>
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