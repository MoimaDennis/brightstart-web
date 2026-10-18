<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';

$stats = [];
foreach (['tblStudent'=>'students','tblVolunteer'=>'volunteers','tblProgram'=>'programs','tblSession'=>'sessions','tblDonation'=>'donations','tblEnrolment'=>'enrolments'] as $tbl=>$key) {
    $r=$conn->query("SELECT COUNT(*) AS c FROM $tbl");
    $stats[$key]=$r?$r->fetch_assoc()['c']:0;
}
$dRes=$conn->query("SELECT SUM(DonationAmount) AS total FROM tblDonation");
$totalDonations=number_format($dRes->fetch_assoc()['total']??0,2);
$recentStudents=$conn->query("SELECT FirstName,LastName,CommunityArea,EnrolmentDate FROM tblStudent ORDER BY EnrolmentDate DESC LIMIT 5");
$recentDonations=$conn->query("SELECT DonorName,DonationAmount,DonationDate,DonationType FROM tblDonation ORDER BY DonationDate DESC LIMIT 5");
$upcomingSessions=$conn->query("SELECT s.SessionID,p.ProgramName,v.FullName AS VolName,s.SessionDate,s.Venue FROM tblSession s JOIN tblProgram p ON s.ProgramID=p.ProgramID JOIN tblVolunteer v ON s.VolunteerID=v.VolunteerID WHERE s.SessionDate>=CURDATE() ORDER BY s.SessionDate ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Bright Start</title>
<link rel="stylesheet" href="css/style.css">
<style>
.hero-slider { position:relative; width:100%; height:480px; overflow:hidden; background:#0a0a0a; }
.slide { position:absolute; inset:0; display:flex; align-items:center; opacity:0; transition:opacity 1.2s ease; background-size:cover; background-position:center; }
.slide.active { opacity:1; }
.slide-1 { background-image:url('images/slide1.jpg'); }
.slide-2 { background-image:url('images/slide2.jpg'); }
.slide-3 { background-image:url('images/slide3.jpg'); }
.slide::before {
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg, rgba(15,30,20,0.78) 0%, rgba(15,30,20,0.30) 60%, rgba(15,30,20,0.55) 100%);
    z-index:1;
}
.slide-content { max-width:1200px; margin:0 auto; padding:0 60px; display:flex; justify-content:flex-start; align-items:center; width:100%; gap:40px; position:relative; z-index:2; }
.slide-text { flex:1; color:#fff; max-width:560px; }
.slide-tag { display:inline-block; font-size:.75rem; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,0.7); margin-bottom:14px; border:1px solid rgba(255,255,255,0.3); padding:4px 12px; border-radius:20px; }
.slide-title { font-size:3rem; font-weight:700; line-height:1.12; margin-bottom:16px; }
.slide-title span { color:var(--accent); }
.slide-subtitle { font-size:1.1rem; color:rgba(255,255,255,0.85); line-height:1.6; margin-bottom:32px; max-width:460px; }
.slide-actions { display:flex; gap:14px; flex-wrap:wrap; }
.slide-btn-primary { background:var(--accent); color:var(--primary); padding:13px 30px; border-radius:30px; text-decoration:none; font-weight:700; font-size:.95rem; border:2px solid var(--accent); transition:all .2s; display:inline-block; }
.slide-btn-primary:hover { background:transparent; color:#fff; }
.slide-btn-secondary { background:transparent; color:#fff; padding:13px 30px; border-radius:30px; text-decoration:none; font-weight:600; font-size:.95rem; border:2px solid rgba(255,255,255,0.5); transition:all .2s; display:inline-block; }
.slide-btn-secondary:hover { border-color:#fff; background:rgba(255,255,255,0.1); }
.slide-stats { display:flex; gap:32px; margin-top:20px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.15); }
.slide-stat .num { font-size:1.5rem; font-weight:700; color:var(--accent); }
.slide-stat .lbl { font-size:.75rem; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:1px; }
.slider-nav { position:absolute; bottom:28px; left:50%; transform:translateX(-50%); display:flex; gap:10px; z-index:20; }
.slider-dot { width:32px; height:3px; background:rgba(255,255,255,0.35); border-radius:2px; cursor:pointer; transition:all .3s; border:none; padding:0; }
.slider-dot.active { background:var(--accent); width:56px; }
.slider-arrow { position:absolute; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.25); color:#fff; width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.2rem; transition:all .2s; z-index:20; }
.slider-arrow:hover { background:rgba(255,255,255,0.25); }
.slider-arrow.prev { left:20px; }
.slider-arrow.next { right:20px; }
.slide-progress { position:absolute; bottom:0; left:0; height:3px; background:var(--accent); width:0%; z-index:20; }
.slide-counter { position:absolute; top:22px; right:28px; color:rgba(255,255,255,0.55); font-size:.82rem; font-weight:600; z-index:20; }
</style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<!-- HERO SLIDER -->
<div class="hero-slider" id="heroSlider">

    <div class="slide slide-1 active">
        <div class="slide-content">
            <div class="slide-text">
                <div class="slide-tag">Education Support System</div>
                <h1 class="slide-title">Empowering<br>Every<br><span>Learner</span></h1>
                <p class="slide-subtitle">Centralised management for students, volunteers, and programs across South Africa's communities.</p>
                <?php if ($currentRole === 'Admin'): ?>
                <div class="slide-actions">
                    <a href="students.php?action=add" class="slide-btn-primary">Register Student</a>
                    <a href="students.php" class="slide-btn-secondary">View All Students</a>
                </div>
                <?php else: ?>
                <div class="slide-actions">
                    <a href="students.php" class="slide-btn-primary">View Students</a>
                    <a href="donate.php" class="slide-btn-secondary">Donate Now</a>
                </div>
                <?php endif; ?>
                <div class="slide-stats">
                    <div class="slide-stat"><div class="num"><?= $stats['students'] ?></div><div class="lbl">Students</div></div>
                    <div class="slide-stat"><div class="num"><?= $stats['programs'] ?></div><div class="lbl">Programs</div></div>
                    <div class="slide-stat"><div class="num"><?= $stats['enrolments'] ?></div><div class="lbl">Enrolments</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="slide slide-2">
        <div class="slide-content">
            <div class="slide-text">
                <div class="slide-tag">Volunteer Management</div>
                <h1 class="slide-title">Dedicated<br>Volunteers,<br><span>Real Impact</span></h1>
                <p class="slide-subtitle">Schedule tutoring sessions, assign volunteers, and track attendance all in one place.</p>
                <?php if ($currentRole === 'Admin'): ?>
                <div class="slide-actions">
                    <a href="sessions.php?action=add" class="slide-btn-primary">Schedule Session</a>
                    <a href="volunteers.php" class="slide-btn-secondary">Manage Volunteers</a>
                </div>
                <?php else: ?>
                <div class="slide-actions">
                    <a href="volunteers.php" class="slide-btn-primary">View Volunteers</a>
                    <a href="attendance.php" class="slide-btn-secondary">Log Attendance</a>
                </div>
                <?php endif; ?>
                <div class="slide-stats">
                    <div class="slide-stat"><div class="num"><?= $stats['volunteers'] ?></div><div class="lbl">Volunteers</div></div>
                    <div class="slide-stat"><div class="num"><?= $stats['sessions'] ?></div><div class="lbl">Sessions</div></div>
                    <div class="slide-stat"><div class="num">100%</div><div class="lbl">Commitment</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="slide slide-3">
        <div class="slide-content">
            <div class="slide-text">
                <div class="slide-tag">Donation Management</div>
                <h1 class="slide-title">Funding<br>The Future<br><span>Together</span></h1>
                <p class="slide-subtitle">Track every donation, generate instant receipts, and produce comprehensive impact reports.</p>
                <?php if ($currentRole === 'Admin'): ?>
                <div class="slide-actions">
                    <a href="donations.php?action=add" class="slide-btn-primary">Record Donation</a>
                    <a href="reports.php" class="slide-btn-secondary">View Reports</a>
                </div>
                <?php else: ?>
                <div class="slide-actions">
                    <a href="donate.php" class="slide-btn-primary">Donate Now</a>
                    <a href="programs.php" class="slide-btn-secondary">View Programs</a>
                </div>
                <?php endif; ?>
                <div class="slide-stats">
                    <div class="slide-stat"><div class="num">R <?= $totalDonations ?></div><div class="lbl">Total Donated</div></div>
                    <div class="slide-stat"><div class="num"><?= $stats['donations'] ?></div><div class="lbl">Donations</div></div>
                    <div class="slide-stat"><div class="num">4</div><div class="lbl">Reports</div></div>
                </div>
            </div>
        </div>
    </div>

    <button class="slider-arrow prev" onclick="changeSlide(-1)">&#8249;</button>
    <button class="slider-arrow next" onclick="changeSlide(1)">&#8250;</button>
    <div class="slider-nav">
        <button class="slider-dot active" onclick="goToSlide(0)"></button>
        <button class="slider-dot" onclick="goToSlide(1)"></button>
        <button class="slider-dot" onclick="goToSlide(2)"></button>
    </div>
    <div class="slide-counter" id="slideCounter">01 / 03</div>
    <div class="slide-progress" id="slideProgress"></div>
</div>

<!-- DASHBOARD -->
<div class="container">
    <div class="stats-grid" style="margin-top:28px;">
        <div class="stat-card green"><div class="stat-icon">👩‍🎓</div><div class="stat-num"><?= $stats['students'] ?></div><div class="stat-label">Students Registered</div></div>
        <div class="stat-card"><div class="stat-icon">🙋</div><div class="stat-num"><?= $stats['volunteers'] ?></div><div class="stat-label">Active Volunteers</div></div>
        <div class="stat-card gold"><div class="stat-icon">📚</div><div class="stat-num"><?= $stats['programs'] ?></div><div class="stat-label">Programs Running</div></div>
        <div class="stat-card"><div class="stat-icon">🗓️</div><div class="stat-num"><?= $stats['sessions'] ?></div><div class="stat-label">Sessions Scheduled</div></div>
        <div class="stat-card orange"><div class="stat-icon">💰</div><div class="stat-num" style="font-size:1.3rem;">R <?= $totalDonations ?></div><div class="stat-label">Total Donations</div></div>
        <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-num"><?= $stats['enrolments'] ?></div><div class="stat-label">Enrolments</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <div class="card">
            <h2>👩‍🎓 Recent Students</h2>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Community</th><th>Enrolled</th></tr></thead>
                <tbody>
                <?php if ($recentStudents && $recentStudents->num_rows>0): while ($s=$recentStudents->fetch_assoc()): ?>
                <tr><td><strong><?= htmlspecialchars($s['FirstName'].' '.$s['LastName']) ?></strong></td><td><?= htmlspecialchars($s['CommunityArea']) ?></td><td><?= date('d M Y',strtotime($s['EnrolmentDate'])) ?></td></tr>
                <?php endwhile; else: ?><tr><td colspan="3" style="text-align:center;padding:16px;color:#999;">No students yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:14px;"><a href="students.php" class="btn btn-primary btn-sm">View All →</a></div>
        </div>
        <div class="card">
            <h2>🗓️ Upcoming Sessions</h2>
            <table class="data-table">
                <thead><tr><th>Program</th><th>Volunteer</th><th>Date</th></tr></thead>
                <tbody>
                <?php if ($upcomingSessions && $upcomingSessions->num_rows>0): while ($s=$upcomingSessions->fetch_assoc()): ?>
                <tr><td><?= htmlspecialchars($s['ProgramName']) ?></td><td><?= htmlspecialchars($s['VolName']) ?></td><td><?= date('d M Y',strtotime($s['SessionDate'])) ?></td></tr>
                <?php endwhile; else: ?><tr><td colspan="3" style="text-align:center;padding:16px;color:#999;">No sessions scheduled.</td></tr><?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:14px;"><a href="sessions.php" class="btn btn-primary btn-sm">View All →</a></div>
        </div>
    </div>

    <?php if ($currentRole === 'Admin'): ?>
    <div class="card">
        <h2>💰 Recent Donations</h2>
        <table class="data-table">
            <thead><tr><th>Donor</th><th>Amount (R)</th><th>Date</th><th>Type</th></tr></thead>
            <tbody>
            <?php if ($recentDonations && $recentDonations->num_rows>0): while ($d=$recentDonations->fetch_assoc()): ?>
            <tr><td><strong><?= htmlspecialchars($d['DonorName']) ?></strong></td><td style="color:var(--success);font-weight:700;">R <?= number_format($d['DonationAmount'],2) ?></td><td><?= date('d M Y',strtotime($d['DonationDate'])) ?></td><td><?= htmlspecialchars($d['DonationType']) ?></td></tr>
            <?php endwhile; else: ?><tr><td colspan="4" style="text-align:center;padding:16px;color:#999;">No donations yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <div style="margin-top:14px;display:flex;gap:10px;">
            <a href="donations.php" class="btn btn-primary btn-sm">View All</a>
            <a href="reports.php?report=donation" class="btn btn-accent btn-sm">📊 Report</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card" style="background:linear-gradient(135deg, var(--primary), var(--secondary)); color:#fff;">
        <h2 style="color:#fff; border-color:var(--accent);">💚 Support Bright Start</h2>
        <p style="margin-bottom:16px; opacity:.9;">Every donation helps a child access quality education. Make a contribution today.</p>
        <a href="donate.php" class="btn" style="background:var(--accent); color:var(--primary); font-weight:700;">Donate Now →</a>
    </div>
    <?php endif; ?>
</div>

<footer><p>&copy; 2026 <span>Bright Start</span> Education Initiative</p></footer>

<script>
const slides=document.querySelectorAll('.slide');
const dots=document.querySelectorAll('.slider-dot');
const counter=document.getElementById('slideCounter');
const progress=document.getElementById('slideProgress');
let current=0; let autoTimer=null;
const INTERVAL=8000;
function pad(n){return String(n).padStart(2,'0');}
function goToSlide(n){
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    current=(n+slides.length)%slides.length;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
    counter.textContent=pad(current+1)+' / '+pad(slides.length);
    resetProgress();
}
function changeSlide(dir){goToSlide(current+dir);resetAuto();}
function resetAuto(){clearInterval(autoTimer);autoTimer=setInterval(()=>goToSlide(current+1),INTERVAL);}
function resetProgress(){
    progress.style.transition='none'; progress.style.width='0%';
    progress.getBoundingClientRect();
    progress.style.transition='width '+INTERVAL+'ms linear';
    progress.style.width='100%';
}
document.addEventListener('keydown',e=>{if(e.key==='ArrowLeft')changeSlide(-1);if(e.key==='ArrowRight')changeSlide(1);});
let touchStartX=0;
document.getElementById('heroSlider').addEventListener('touchstart',e=>{touchStartX=e.touches[0].clientX;},{passive:true});
document.getElementById('heroSlider').addEventListener('touchend',e=>{const diff=touchStartX-e.changedTouches[0].clientX;if(Math.abs(diff)>50){changeSlide(diff>0?1:-1);resetAuto();}},{passive:true});
resetAuto(); resetProgress();
</script>
</body>
</html>