<?php
require_once 'includes/auth_guard.php';
require_once 'includes/DBConn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings – Bright Start</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="container">
    <h1 class="page-title">⚙️ <em>Settings</em></h1>
    <p class="page-subtitle">Choose how Bright Start looks for you and manage your account. Changes are saved to your account and follow you on every device.</p>

    <div class="settings-grid">
        <!-- Appearance -->
        <div class="card">
            <h2>🎨 Appearance</h2>
            <div id="settingsMsg" class="alert" style="display:none;"></div>
            <form id="settingsForm" novalidate>
                <div class="form-group">
                    <label for="theme">Theme</label>
                    <select id="theme" name="theme">
                        <option value="dark">Dark (default)</option>
                        <option value="light">Light</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="text_size">Text size</label>
                    <select id="text_size" name="text_size">
                        <option value="normal">Normal</option>
                        <option value="large">Large</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="show_slider">Dashboard banner slider</label>
                    <select id="show_slider" name="show_slider">
                        <option value="1">Show</option>
                        <option value="0">Hide</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">💾 Save Settings</button>
            </form>
        </div>

        <!-- Profile -->
        <div class="card">
            <h2>👤 My Profile</h2>
            <div id="profileMsg" class="alert" style="display:none;"></div>
            <form id="profileForm" novalidate>
                <div class="form-group">
                    <label for="full_name">Full name</label>
                    <input id="full_name" type="text" name="full_name" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label>Email (cannot be changed)</label>
                    <input id="p_email" type="text" disabled>
                </div>
                <div class="form-grid-2">
                    <div class="form-group"><label>Role</label><input id="p_role" type="text" disabled></div>
                    <div class="form-group"><label>Sign-in method</label><input id="p_provider" type="text" disabled></div>
                </div>
                <button type="submit" class="btn btn-primary">💾 Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Password -->
    <div class="card" id="passwordCard">
        <h2>🔒 Change Password</h2>
        <div id="passwordMsg" class="alert" style="display:none;"></div>
        <form id="passwordForm" novalidate>
            <div class="form-grid-3">
                <div class="form-group"><label for="current_password">Current password</label><input id="current_password" type="password" autocomplete="current-password" required></div>
                <div class="form-group"><label for="new_password">New password</label><input id="new_password" type="password" autocomplete="new-password" placeholder="8+ characters, letters and numbers" required></div>
                <div class="form-group"><label for="confirm_password">Confirm new password</label><input id="confirm_password" type="password" autocomplete="new-password" required></div>
            </div>
            <button type="submit" class="btn btn-primary">🔑 Change Password</button>
        </form>
        <p id="googleNote" style="display:none;color:var(--text-light);">You signed in with Google, so your password is managed by Google.</p>
    </div>

    <p style="color:var(--text-light);">Need help? <a href="help.php" style="color:var(--accent);">Open the Help Centre →</a></p>
</div>

<footer><p>&copy; 2026 <span>Bright Start</span> Education Initiative</p></footer>

<script>
function showMsg(id, text, type) {
    var el = document.getElementById(id);
    el.className = 'alert alert-' + type;
    el.textContent = text;
    el.style.display = 'block';
}
async function api(url, body) {
    var opts = body ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) } : {};
    var res = await fetch(url, opts);
    return res.json();
}
function applyLook(theme, size, slider) {
    document.body.classList.remove('theme-dark', 'theme-light', 'text-normal', 'text-large');
    document.body.classList.add('theme-' + theme, 'text-' + size);
    var s = document.querySelector('.hero-slider'); if (s) s.style.display = slider ? '' : 'none';
}

// 1. Load settings + profile from the API
(async function load() {
    try {
        var d = await api('api/get_settings.php');
        if (!d.success) { showMsg('settingsMsg', d.message, 'danger'); return; }
        document.getElementById('theme').value       = d.settings.theme;
        document.getElementById('text_size').value   = d.settings.text_size;
        document.getElementById('show_slider').value = String(d.settings.show_slider);
        document.getElementById('full_name').value   = d.profile.FullName;
        document.getElementById('p_email').value     = d.profile.Email;
        document.getElementById('p_role').value      = d.profile.Role;
        document.getElementById('p_provider').value  = d.profile.AuthProvider === 'google' ? 'Google (SSO)' : 'Email and password';
        if (d.profile.AuthProvider !== 'local') {
            document.getElementById('passwordForm').style.display = 'none';
            document.getElementById('googleNote').style.display = 'block';
        }
    } catch (e) { showMsg('settingsMsg', 'Could not load your settings. Please refresh the page.', 'danger'); }
})();

// 2. Save appearance settings
document.getElementById('settingsForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    var theme = document.getElementById('theme').value, size = document.getElementById('text_size').value,
        slider = parseInt(document.getElementById('show_slider').value, 10);
    try {
        var d = await api('api/update_settings.php', { theme: theme, text_size: size, show_slider: slider });
        showMsg('settingsMsg', d.success ? '✅ Settings saved.' : d.message, d.success ? 'success' : 'danger');
        if (d.success) applyLook(theme, size, slider);
    } catch (err) { showMsg('settingsMsg', 'Could not save. Please try again.', 'danger'); }
});

// 3. Update profile
document.getElementById('profileForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    var name = document.getElementById('full_name').value.trim();
    if (name.length < 2) { showMsg('profileMsg', 'Please enter your full name.', 'danger'); return; }
    try {
        var d = await api('api/update_profile.php', { full_name: name });
        showMsg('profileMsg', d.success ? '✅ Profile updated. (Your new name shows on the next page.)' : d.message, d.success ? 'success' : 'danger');
    } catch (err) { showMsg('profileMsg', 'Could not save. Please try again.', 'danger'); }
});

// 4. Change password
document.getElementById('passwordForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    var cur = document.getElementById('current_password').value, nw = document.getElementById('new_password').value,
        cf = document.getElementById('confirm_password').value;
    if (!cur || !nw || !cf) { showMsg('passwordMsg', 'Please fill in all three password fields.', 'danger'); return; }
    try {
        var d = await api('api/change_password.php', { current_password: cur, new_password: nw, confirm_password: cf });
        showMsg('passwordMsg', d.success ? '✅ ' + d.message : d.message, d.success ? 'success' : 'danger');
        if (d.success) document.getElementById('passwordForm').reset();
    } catch (err) { showMsg('passwordMsg', 'Could not change the password. Please try again.', 'danger'); }
});
</script>
</body>
</html>