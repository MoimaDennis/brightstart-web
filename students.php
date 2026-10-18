<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg     = '';
$msgType = 'success';
$action  = $_GET['action'] ?? 'list';
if ($currentRole !== 'Admin' && $action !== 'list') {
    header("Location: " . basename($_SERVER['PHP_SELF']));
    exit();
}
$editStudent = null;

// ---- ADD ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $fn   = trim($_POST['first_name']);
    $ln   = trim($_POST['last_name']);
    $dob  = trim($_POST['dob']);
    $gen  = trim($_POST['gender']);
    $area = trim($_POST['community_area']);
    $cont = trim($_POST['contact_number']);
    $enrl = trim($_POST['enrolment_date']);

    $stmt = $conn->prepare(
        "INSERT INTO tblstudent
         (FirstName,LastName,DateOfBirth,Gender,CommunityArea,ContactNumber,EnrolmentDate)
         VALUES (?,?,?,?,?,?,?)"
    );
    $stmt->bind_param("sssssss", $fn,$ln,$dob,$gen,$area,$cont,$enrl);
    if ($stmt->execute()) {
        $msg = "Student '$fn $ln' registered successfully.";
    } else {
        $msg = 'Error: ' . $stmt->error;
        $msgType = 'danger';
    }
    $stmt->close();
    $action = 'list';
}

// ---- UPDATE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $id   = (int)$_POST['student_id'];
    $fn   = trim($_POST['first_name']);
    $ln   = trim($_POST['last_name']);
    $dob  = trim($_POST['dob']);
    $gen  = trim($_POST['gender']);
    $area = trim($_POST['community_area']);
    $cont = trim($_POST['contact_number']);
    $enrl = trim($_POST['enrolment_date']);


        $stmt = $conn->prepare(
        "UPDATE tblstudent
         SET FirstName=?,LastName=?,DateOfBirth=?,Gender=?,
             CommunityArea=?,ContactNumber=?,EnrolmentDate=?
         WHERE StudentID=?"
    );
    $stmt->bind_param("sssssss i", $fn,$ln,$dob,$gen,$area,$cont,$enrl,$id);
    $stmt->close();

    $stmt2 = $conn->prepare(
        "UPDATE tblstudent
         SET FirstName=?,LastName=?,DateOfBirth=?,Gender=?,
             CommunityArea=?,ContactNumber=?,EnrolmentDate=?
         WHERE StudentID=?"
    );
    $stmt2->bind_param("sssssssi", $fn,$ln,$dob,$gen,$area,$cont,$enrl,$id);
    if ($stmt2->execute()) {
        $msg = "Student record updated successfully.";
    } else {
        $msg = 'Error: ' . $stmt2->error;
        $msgType = 'danger';
    }
    $stmt2->close();
    $action = 'list';
}

// ---- DELETE ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tblstudent WHERE StudentID=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = 'Student deleted successfully.';
    } else {
        $msg = 'Error: ' . $stmt->error;
        $msgType = 'danger';
    }
    $stmt->close();
    $action = 'list';
}

// ---- LOAD FOR EDIT ----
if ($action === 'edit' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $res = $conn->query("SELECT * FROM tblstudent WHERE StudentID=$id");
    $editStudent = $res->fetch_assoc();
}

// ---- SEARCH ----
$search     = trim($_GET['search'] ?? '');
$filterArea = trim($_GET['area']   ?? '');
$where = '1=1';
if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (FirstName LIKE '%$s%' OR LastName LIKE '%$s%')";
}
if ($filterArea) {
    $a = $conn->real_escape_string($filterArea);
    $where .= " AND CommunityArea LIKE '%$a%'";
}
$students = $conn->query(
    "SELECT * FROM tblstudent WHERE $where ORDER BY EnrolmentDate DESC"
);
$totalStudents = $conn->query(
    "SELECT COUNT(*) AS c FROM tblstudent"
)->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students – Bright Start</title>
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

    <!-- ADD / EDIT FORM -->
    <div class="card">
        <h2>
            <?= $action === 'edit'
                ? '✏️ Edit Student Record'
                : '➕ Register New Student' ?>
        </h2>

        <form method="POST" action="students.php">
            <input type="hidden" name="action"
                   value="<?= $action === 'edit' ? 'update' : 'add' ?>">
            <?php if ($editStudent): ?>
                <input type="hidden" name="student_id"
                       value="<?= $editStudent['StudentID'] ?>">
            <?php endif; ?>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" required
                           value="<?= htmlspecialchars(
                               $editStudent['FirstName'] ?? '') ?>"
                           placeholder="Thabo">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required
                           value="<?= htmlspecialchars(
                               $editStudent['LastName'] ?? '') ?>"
                           placeholder="Nkosi">
                </div>
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required
                           value="<?= $editStudent['DateOfBirth'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>"
                                <?= ($editStudent['Gender'] ?? '') === $g
                                    ? 'selected' : '' ?>>
                                <?= $g ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Community Area *</label>
                    <input type="text" name="community_area" required
                           value="<?= htmlspecialchars(
                               $editStudent['CommunityArea'] ?? '') ?>"
                           placeholder="Soweto, Gauteng">
                </div>
                <div class="form-group">
                    <label>Contact Number *</label>
                    <input type="text" name="contact_number" required
                           value="<?= htmlspecialchars(
                               $editStudent['ContactNumber'] ?? '') ?>"
                           placeholder="0712345678">
                </div>
                <div class="form-group">
                    <label>Enrolment Date *</label>
                    <input type="date" name="enrolment_date" required
                           value="<?= $editStudent['EnrolmentDate']
                               ?? date('Y-m-d') ?>">
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action === 'edit'
                        ? '💾 Update Student'
                        : '✅ Register Student' ?>
                </button>
                <a href="students.php" class="btn btn-warning">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <?php else: ?>

    <!-- LIST VIEW -->
    <div class="card">
        <h2>👩‍🎓 Student Register
            <span style="font-size:.85rem; font-weight:400;
                          color:#666; margin-left:8px;">
                (<?= $totalStudents ?> total)
            </span>
        </h2>

        <!-- Search Bar -->
        <form method="GET" action="students.php"
              style="display:flex; gap:10px; margin-bottom:18px;
                     flex-wrap:wrap; align-items:flex-end;">
            <input type="text" name="search"
                   placeholder="Search by name..."
                   value="<?= htmlspecialchars($search) ?>"
                   style="padding:8px 12px; border:1.5px solid #ccc;
                          border-radius:6px; font-size:.9rem; width:200px;">
            <input type="text" name="area"
                   placeholder="Filter by community..."
                   value="<?= htmlspecialchars($filterArea) ?>"
                   style="padding:8px 12px; border:1.5px solid #ccc;
                          border-radius:6px; font-size:.9rem; width:200px;">
            <button type="submit" class="btn btn-primary btn-sm">
                🔍 Search
            </button>
            <a href="students.php" class="btn btn-sm"
               style="background:#eee; color:#333;">
                Clear
            </a>
            <a href="students.php?action=add"
               class="btn btn-accent btn-sm"
               style="margin-left:auto;">
                + Register Student
            </a>
        </form>

        <!-- Students Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Community Area</th>
                    <th>Contact</th>
                    <th>Enrolled</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($students && $students->num_rows > 0):
                while ($s = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= $s['StudentID'] ?></td>
                    <td><strong><?= htmlspecialchars($s['FirstName']) ?></strong></td>
                    <td><?= htmlspecialchars($s['LastName']) ?></td>
                    <td><?= date('d M Y', strtotime($s['DateOfBirth'])) ?></td>
                    <td><?= htmlspecialchars($s['Gender']) ?></td>
                    <td><?= htmlspecialchars($s['CommunityArea']) ?></td>
                    <td><?= htmlspecialchars($s['ContactNumber']) ?></td>
                    <td><?= date('d M Y', strtotime($s['EnrolmentDate'])) ?></td>
                    <td style="white-space:nowrap;">
                        <a href="students.php?action=edit&id=<?= $s['StudentID'] ?>"
                           class="btn btn-warning btn-sm">
                            ✏️ Edit
                        </a>
                        <a href="students.php?delete=<?= $s['StudentID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm(
                               'Delete this student?')">
                            🗑️
                        </a>
                        <a href="performance.php?student_id=<?= $s['StudentID'] ?>"
                           class="btn btn-primary btn-sm">
                            📊
                        </a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="9"
                        style="text-align:center; padding:24px; color:#999;">
                        No students found.
                        <a href="students.php?action=add">
                            Register one →
                        </a>
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