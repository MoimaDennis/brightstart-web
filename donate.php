<?php
// ============================================================
// donate.php – Public Donation Page (any logged-in user)
// ============================================================
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$msg = ''; $msgType = 'success';
$showReceipt = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donorName    = trim($_POST['donor_name']);
    $donorContact = trim($_POST['donor_contact']);
    $amount       = (float)$_POST['donation_amount'];
    $type         = trim($_POST['donation_type']);
    $notes        = trim($_POST['notes']);
    $date         = date('Y-m-d');

    if ($amount <= 0) {
        $msg = 'Please enter a valid donation amount.';
        $msgType = 'danger';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO tbldonation (DonorName,DonorContact,DonationAmount,DonationDate,DonationType,Notes)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssdsss", $donorName, $donorContact, $amount, $date, $type, $notes);
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $msg = "Thank you, $donorName! Your donation has been recorded.";
            $rRes = $conn->query("SELECT * FROM tbldonation WHERE DonationID=$newId");
            $showReceipt = $rRes->fetch_assoc();
        } else {
            $msg = 'Error: ' . $stmt->error;
            $msgType = 'danger';
        }
        $stmt->close();
    }
}

$myDonations = $conn->query(
    "SELECT * FROM tbldonation WHERE DonorContact='" . $conn->real_escape_string($currentEmail) . "'
     ORDER BY DonationDate DESC"
);
$myTotal = 0;
$rows = [];
if ($myDonations) {
    while ($r = $myDonations->fetch_assoc()) { $rows[] = $r; $myTotal += $r['DonationAmount']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate – Bright Start</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="container">

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($showReceipt): ?>
    <div class="card" style="border:2px solid var(--accent); max-width:600px; margin:0 auto 24px;">
        <div style="text-align:center; border-bottom:2px solid #eee; padding-bottom:14px; margin-bottom:16px;">
            <strong style="font-size:1.2rem; color:var(--primary);">Bright Start Education Initiative</strong><br>
            <span style="color:#888; font-size:.85rem;">Donation Receipt</span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:.93rem;">
            <div><strong>Receipt #:</strong> REC-<?= str_pad($showReceipt['DonationID'],6,'0',STR_PAD_LEFT) ?></div>
            <div><strong>Date:</strong> <?= date('d M Y', strtotime($showReceipt['DonationDate'])) ?></div>
            <div><strong>Donor Name:</strong> <?= htmlspecialchars($showReceipt['DonorName']) ?></div>
            <div><strong>Contact:</strong> <?= htmlspecialchars($showReceipt['DonorContact']) ?></div>
            <div><strong>Type:</strong> <?= htmlspecialchars($showReceipt['DonationType']) ?></div>
            <div><strong>Amount:</strong>
                <span style="color:var(--success); font-size:1.2rem; font-weight:700;">
                    R <?= number_format($showReceipt['DonationAmount'],2) ?>
                </span>
            </div>
        </div>
        <div style="margin-top:18px; padding:12px; background:var(--light-bg); border-radius:8px; text-align:center; font-size:.88rem; color:#555;">
            Thank you for supporting Bright Start Education Initiative! 💚
        </div>
        <div style="margin-top:14px; text-align:center;">
            <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Print Receipt</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width:600px; margin:0 auto;">
        <h2>💚 Make a Donation</h2>
        <p style="color:var(--text-light); margin-bottom:18px;">
            Support Bright Start Education Initiative directly. Every contribution helps a child access quality education.
        </p>

        <form method="POST" action="donate.php">
            <div class="form-group">
                <label>Your Name *</label>
                <input type="text" name="donor_name" required maxlength="100"
                       value="<?= htmlspecialchars($currentUser) ?>">
            </div>
            <div class="form-group">
                <label>Contact Email *</label>
                <input type="email" name="donor_contact" required maxlength="100"
                       value="<?= htmlspecialchars($currentEmail) ?>">
            </div>
            <div class="form-group">
                <label>Donation Amount (R) *</label>
                <input type="number" name="donation_amount" required min="1" step="0.01" placeholder="100.00">
            </div>
            <div class="form-group">
                <label>Donation Type *</label>
                <select name="donation_type">
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="In-Kind">In-Kind</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notes (optional)</label>
                <textarea name="notes" rows="2" placeholder="Any message..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-full">💚 Donate Now</button>
        </form>
    </div>

    <div class="card" style="max-width:600px; margin:24px auto 0;">
        <h2>📋 My Donation History</h2>
        <table class="data-table">
            <thead><tr><th>Date</th><th>Amount (R)</th><th>Type</th></tr></thead>
            <tbody>
            <?php if (!empty($rows)): foreach ($rows as $r): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($r['DonationDate'])) ?></td>
                    <td style="color:var(--success); font-weight:700;">R <?= number_format($r['DonationAmount'],2) ?></td>
                    <td><?= htmlspecialchars($r['DonationType']) ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" style="text-align:center; padding:16px; color:#999;">You haven't made any donations yet.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot>
                <tr><td><strong>Total</strong></td><td style="color:var(--success); font-weight:700;">R <?= number_format($myTotal,2) ?></td><td></td></tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<footer><p>&copy; 2026 <span>Bright Start</span> Education Initiative</p></footer>
</body>
</html>