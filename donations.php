<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';
if ($currentRole !== 'Admin') { header("Location: index.php"); exit(); }

$msg=''; $msgType='success';
$action=$_GET['action']??'list';
$showReceipt=null;

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])
    && $_POST['action']==='add') {
    $dn =trim($_POST['donor_name']);
    $dc =trim($_POST['donor_contact']);
    $amt=(float)$_POST['donation_amount'];
    $dd =trim($_POST['donation_date']);
    $dt =trim($_POST['donation_type']);
    $nt =trim($_POST['notes']);

    $stmt=$conn->prepare(
        "INSERT INTO tblDonation
         (DonorName,DonorContact,DonationAmount,DonationDate,DonationType,Notes)
         VALUES (?,?,?,?,?,?)"
    );
    $stmt->bind_param("ssdsss",$dn,$dc,$amt,$dd,$dt,$nt);
    if ($stmt->execute()) {
        $newId=$stmt->insert_id;
        $msg="Donation from '$dn' recorded.";
        $rRes=$conn->query(
            "SELECT * FROM tblDonation WHERE DonationID=$newId"
        );
        $showReceipt=$rRes->fetch_assoc();
    } else {
        $msg='Error: '.$stmt->error; $msgType='danger';
    }
    $stmt->close();
    $action='list';
}

if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $stmt=$conn->prepare("DELETE FROM tblDonation WHERE DonationID=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) { $msg='Donation deleted.'; }
    else { $msg='Error: '.$stmt->error; $msgType='danger'; }
    $stmt->close();
}

if (isset($_GET['receipt'])) {
    $id=(int)$_GET['receipt'];
    $rRes=$conn->query("SELECT * FROM tblDonation WHERE DonationID=$id");
    $showReceipt=$rRes->fetch_assoc();
}

$donations=$conn->query(
    "SELECT * FROM tblDonation ORDER BY DonationDate DESC"
);
$total=$conn->query(
    "SELECT SUM(DonationAmount) AS t FROM tblDonation"
)->fetch_assoc()['t']??0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations – Bright Start</title>
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

    <!-- RECEIPT -->
    <?php if ($showReceipt): ?>
    <div class="card" style="border:2px solid #f0a500;
                              max-width:600px; margin:0 auto 24px;">
        <div style="text-align:center; border-bottom:2px solid #eee;
                    padding-bottom:14px; margin-bottom:16px;">
            <div style="font-size:2rem;">🎓</div>
            <strong style="font-size:1.2rem; color:#1a3c5e;">
                Bright Start Education Initiative
            </strong><br>
            <span style="color:#888; font-size:.85rem;">
                Official Donation Receipt
            </span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr;
                    gap:10px; font-size:.93rem;">
            <div>
                <strong>Receipt #:</strong>
                REC-<?= str_pad($showReceipt['DonationID'],6,'0',STR_PAD_LEFT) ?>
            </div>
            <div>
                <strong>Date:</strong>
                <?= date('d M Y',strtotime($showReceipt['DonationDate'])) ?>
            </div>
            <div>
                <strong>Donor Name:</strong>
                <?= htmlspecialchars($showReceipt['DonorName']) ?>
            </div>
            <div>
                <strong>Contact:</strong>
                <?= htmlspecialchars($showReceipt['DonorContact']) ?>
            </div>
            <div>
                <strong>Type:</strong>
                <?= htmlspecialchars($showReceipt['DonationType']) ?>
            </div>
            <div>
                <strong>Amount:</strong>
                <span style="color:#2e7d32; font-size:1.2rem;
                              font-weight:700;">
                    R <?= number_format($showReceipt['DonationAmount'],2) ?>
                </span>
            </div>
        </div>
        <?php if ($showReceipt['Notes']): ?>
        <p style="margin-top:12px; font-size:.88rem; color:#666;">
            <strong>Notes:</strong>
            <?= htmlspecialchars($showReceipt['Notes']) ?>
        </p>
        <?php endif; ?>
        <div style="margin-top:18px; padding:12px;
                    background:#f0f4f8; border-radius:8px;
                    text-align:center; font-size:.88rem; color:#555;">
            Thank you for your generous contribution to
            Bright Start Education Initiative. 💛
        </div>
        <div style="margin-top:14px; display:flex;
                    gap:10px; justify-content:center;">
            <button onclick="window.print()"
                    class="btn btn-primary btn-sm">
                🖨️ Print Receipt
            </button>
            <a href="donations.php"
               class="btn btn-sm" style="background:#eee;">
                Close
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ADD FORM -->
    <?php if ($action==='add'): ?>
    <div class="card">
        <h2>💰 Record Donation</h2>
        <form method="POST" action="donations.php">
            <input type="hidden" name="action" value="add">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Donor Name *</label>
                    <input type="text" name="donor_name" required
                           placeholder="Thandi Mokoena" maxlength="100">
                </div>
                <div class="form-group">
                    <label>Donor Contact *</label>
                    <input type="text" name="donor_contact" required
                           placeholder="thandi@example.co.za"
                           maxlength="100">
                </div>
                <div class="form-group">
                    <label>Donation Amount (R) *</label>
                    <input type="number" name="donation_amount"
                           required min="1" step="0.01"
                           placeholder="5000.00">
                </div>
                <div class="form-group">
                    <label>Donation Date *</label>
                    <input type="date" name="donation_date"
                           required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Donation Type *</label>
                    <select name="donation_type">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="In-Kind">In-Kind</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notes (optional)</label>
                <textarea name="notes" rows="2"
                          placeholder="Any details..."></textarea>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">
                    ✅ Record &amp; Generate Receipt
                </button>
                <a href="donations.php" class="btn btn-warning">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <?php else: ?>
    <!-- LIST -->
    <div class="card">
        <h2>💰 Donation History</h2>
        <div style="margin-bottom:16px; display:flex;
                    justify-content:space-between; align-items:center;">
            <a href="donations.php?action=add" class="btn btn-accent">
                + Record Donation
            </a>
            <div style="font-size:1.1rem; font-weight:700;
                        color:#2e7d32;">
                Total: R <?= number_format($total,2) ?>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Donor</th><th>Amount (R)</th><th>Date</th>
                    <th>Type</th><th>Notes</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($donations && $donations->num_rows>0):
                while ($d=$donations->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['DonorName']) ?></strong></td>
                    <td style="color:#2e7d32; font-weight:700;">
                        R <?= number_format($d['DonationAmount'],2) ?>
                    </td>
                    <td><?= date('d M Y',strtotime($d['DonationDate'])) ?></td>
                    <td><?= htmlspecialchars($d['DonationType']) ?></td>
                    <td style="font-size:.83rem;">
                        <?= htmlspecialchars($d['Notes']??'') ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="donations.php?receipt=<?= $d['DonationID'] ?>"
                           class="btn btn-primary btn-sm">
                            🧾 Receipt
                        </a>
                        <a href="donations.php?delete=<?= $d['DonationID'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete?')">
                            🗑️
                        </a>
                    </td>
                </tr>
            <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="6"
                        style="text-align:center;padding:20px;color:#999;">
                        No donations yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="1">
                        <strong>Grand Total</strong>
                    </td>
                    <td style="color:#2e7d32; font-weight:700;">
                        R <?= number_format($total,2) ?>
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>
<footer>
    <p>&copy; 2026 <span>Bright Start</span> Education Initiative</p>
</footer>
</body>
</html>