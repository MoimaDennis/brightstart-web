<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

if ($currentRole !== 'Admin') {
    header("Location: index.php");
    exit();
}

$msg      = '';
$msgType  = 'success';
$action   = $_GET['action'] ?? 'list';
$tab      = $_GET['tab']    ?? 'all';
$editUser = null;

// ---- VERIFY USER ----
if (isset($_GET['verify'])) {
    $id   = (int)$_GET['verify'];
    $stmt = $conn->prepare("UPDATE Administrator SET Verified = 1 WHERE AdminID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = 'User verified successfully. They can now login.';
    } else {
        $msg     = 'Error: ' . $stmt->error;
        $msgType = 'danger';
    }
    $stmt->close();
    $tab = 'pending';
}

// ---- REJECT PENDING USER ----
if (isset($_GET['reject'])) {
    $id   = (int)$_GET['reject'];
    $stmt = $conn->prepare("DELETE FROM Administrator WHERE AdminID = ? AND Verified = 0");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = 'Registration rejected and removed.';
    } else {
        $msg     = 'Error: ' . $stmt->error;
        $msgType = 'danger';
    }
    $stmt->close();
    $tab = 'pending';
}

// ---- ADD USER ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $fn   = trim($_POST['full_name']);
    $em   = trim($_POST['email']);
    $un   = trim($_POST['username']);
    $pw   = trim($_POST['password']);
    $role = trim($_POST['role']);
    $hash = password_hash($pw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO Administrator (FullName, Email, Username, PasswordHash, Role, Verified)
         VALUES (?, ?, ?, ?, ?, 1)"
    );
    $stmt->bind_param("sssss", $fn, $em, $un, $hash, $role);
    if ($stmt->execute()) {
        $msg = "User '$fn' added and automatically verified.";
    } else {
        $msg     = 'Error: ' . $stmt->error;
        $msgType = 'danger';
    }
    $stmt->close();
    $action = 'list';
}

// ---- UPDATE USER ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id       = (int)$_POST['user_id'];
    $fn       = trim($_POST['full_name']);
    $em       = trim($_POST['email']);
    $un       = trim($_POST['username']);
    $role     = trim($_POST['role']);
    $verified = isset($_POST['verified']) ? 1 : 0;

    $stmt = $conn->prepare(
        "UPDATE Administrator SET FullName=?, Email=?, Username=?, Role=?, Verified=? WHERE AdminID=?"
    );
    $stmt->bind_param("ssssii", $fn, $em, $un, $role, $verified, $id);
    if ($stmt->execute()) {
        $msg = "User updated successfully.";
    } else {
        $msg     = 'Error: ' . $stmt->error;
        $msgType = 'danger';
    }
    $stmt->close();
    $action = 'list';
}

// ---- DELETE USER ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['admin_id']) {
        $stmt = $conn->prepare("DELETE FROM Administrator WHERE AdminID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $msg = 'User deleted.';
        } else {
            $msg     = 'Error: ' . $stmt->error;
            $msgType = 'danger';
        }
        $stmt->close();
    } else {
        $msg     = 'You cannot delete your own account.';
        $msgType = 'warning';
    }
}

// ---- LOAD EDIT ----
if ($action === 'edit' && isset($_GET['id'])) {
    $res      = $conn->query("SELECT * FROM Administrator WHERE AdminID=" . (int)$_GET['id']);
    $editUser = $res->fetch_assoc();
}

// ---- FETCH DATA ----
$allUsers     = $conn->query("SELECT * FROM Administrator WHERE Verified = 1 ORDER BY Role, FullName");
$pendingUsers = $conn->query("SELECT * FROM Administrator WHERE Verified = 0 ORDER BY CreatedAt DESC");
$pendingCount = $pendingUsers->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users – Bright Start</title>
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

    <?php if ($pendingCount > 0): ?>
        <div class="alert alert-warning">
            ⚠️ <strong><?= $pendingCount ?></strong> new registration(s) are waiting for your verification.
            <a href="admin_users.php?tab=pending"
               style="font-weight:700; color:#e65100; margin-left:8px;">
                Review them now →
            </a>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>

    <!-- ======== ADD / EDIT FORM ======== -->
    <div class="card">
        <h2><?= $action === 'edit' ? '✏️ Edit User' : '➕ Add New User' ?></h2>
        <form method="POST" action="admin_users.php">
            <input type="hidden" name="action"
                   value="<?= $action === 'edit' ? 'update' : 'add' ?>">
            <?php if ($editUser): ?>
                <input type="hidden" name="user_id"
                       value="<?= $editUser['AdminID'] ?>">
            <?php endif; ?>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required
                           value="<?= htmlspecialchars($editUser['FullName'] ?? '') ?>"
                           placeholder="Thabo Nkosi">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required
                           value="<?= htmlspecialchars($editUser['Email'] ?? '') ?>"
                           placeholder="thabo@brightstart.org">
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required
                           value="<?= htmlspecialchars($editUser['Username'] ?? '') ?>"
                           placeholder="thabo123">
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <?php
                        foreach (['Admin','Coordinator','Volunteer','Management'] as $r):
                        ?>
                            <option value="<?= $r ?>"
                                <?= ($editUser['Role'] ?? '') === $r
                                    ? 'selected' : '' ?>>
                                <?= $r ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($action === 'add'): ?>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required
                           placeholder="Min 6 characters">
                </div>
                <?php endif; ?>
                <?php if ($action === 'edit'): ?>
                <div class="form-group">
                    <label style="display:flex; align-items:center;
                                  gap:8px; cursor:pointer;">
                        <input type="checkbox" name="verified" value="1"
                               <?= ($editUser['Verified'] ?? 0)
                                   ? 'checked' : '' ?>>
                        Mark as Verified (allow login)
                    </label>
                </div>
                <?php endif; ?>
            </div>

            <div style="display:flex; gap:10px; margin-top:6px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action === 'edit' ? '💾 Update User' : '➕ Add User' ?>
                </button>
                <a href="admin_users.php" class="btn btn-warning">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <?php else: ?>

    <!-- ======== LIST VIEW ======== -->
    <div class="card">
        <h2>⚙️ System Users</h2>

        <!-- Tabs -->
        <div class="tabs">
            <a href="admin_users.php?tab=all"
               class="tab-link <?= $tab === 'all' ? 'active' : '' ?>">
                ✅ Verified Users (<?= $allUsers->num_rows ?>)
            </a>
            <a href="admin_users.php?tab=pending"
               class="tab-link <?= $tab === 'pending' ? 'active' : '' ?>"
               style="<?= $pendingCount > 0
                   ? 'color:#e65100; font-weight:700;' : '' ?>">
                ⏳ Pending Verification
                <?php if ($pendingCount > 0): ?>
                    <span style="background:#e65100; color:#fff;
                                 border-radius:12px; padding:2px 8px;
                                 font-size:.75rem; margin-left:4px;">
                        <?= $pendingCount ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- ======== PENDING TAB ======== -->
        <?php if ($tab === 'pending'): ?>

            <?php if ($pendingCount === 0): ?>
                <div class="alert alert-info">
                    ✅ No pending registrations at this time.
                </div>
            <?php else: ?>

            <p style="color:#666; margin-bottom:16px; font-size:.9rem;">
                These users registered themselves and are waiting for your
                approval. Click <strong>Verify</strong> to allow them to
                login or <strong>Reject</strong> to remove them.
            </p>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Requested Role</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $pendingUsers->data_seek(0);
                while ($u = $pendingUsers->fetch_assoc()):
                ?>
                    <tr style="background:#fff8e1;">
                        <td>
                            <strong>
                                <?= htmlspecialchars($u['FullName']) ?>
                            </strong>
                        </td>
                        <td><?= htmlspecialchars($u['Email']) ?></td>
                        <td><?= htmlspecialchars($u['Username']) ?></td>
                        <td>
                            <span class="badge-pending">
                                <?= htmlspecialchars($u['Role']) ?>
                            </span>
                        </td>
                        <td>
                            <?= date('d M Y H:i',
                                strtotime($u['CreatedAt'])) ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="admin_users.php?verify=<?= $u['AdminID'] ?>&tab=pending"
                               class="btn btn-success btn-sm"
                               onclick="return confirm(
                                   'Verify this user and allow them to login?'
                               )">
                                ✅ Verify
                            </a>
                            <a href="admin_users.php?action=edit&id=<?= $u['AdminID'] ?>"
                               class="btn btn-warning btn-sm">
                                ✏️ Edit
                            </a>
                            <a href="admin_users.php?reject=<?= $u['AdminID'] ?>&tab=pending"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm(
                                   'Reject and delete this registration?'
                               )">
                                ✖ Reject
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <?php endif; ?>

        <!-- ======== VERIFIED USERS TAB ======== -->
        <?php else: ?>

        <div style="margin-bottom:14px;">
            <a href="admin_users.php?action=add"
               class="btn btn-accent">
                + Add User Directly
            </a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $allUsers->data_seek(0);
            while ($u = $allUsers->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $u['AdminID'] ?></td>
                    <td>
                        <strong>
                            <?= htmlspecialchars($u['FullName']) ?>
                        </strong>
                        <?php if ($u['AdminID'] == $_SESSION['admin_id']): ?>
                            <span style="font-size:.75rem;
                                         color:#1a3c5e;
                                         font-weight:700;">
                                (You)
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($u['Email']) ?></td>
                    <td><?= htmlspecialchars($u['Username']) ?></td>
                    <td>
                        <span class="badge-active">
                            <?= htmlspecialchars($u['Role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge-active">✅ Verified</span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="admin_users.php?action=edit&id=<?= $u['AdminID'] ?>"
                           class="btn btn-warning btn-sm">
                            ✏️ Edit
                        </a>
                        <?php if ($u['AdminID'] != $_SESSION['admin_id']): ?>
                        <a href="admin_users.php?delete=<?= $u['AdminID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this user?')">
                            🗑️ Delete
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <?php endif; ?>

    </div>
    <?php endif; ?>

</div>

<footer>
    <p>&copy; 2026 <span>Bright Start</span> Education Initiative</p>
</footer>
</body>
</html>