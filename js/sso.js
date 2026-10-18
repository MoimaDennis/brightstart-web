// Single sign-on with Google (Firebase Authentication), then hand the token to our own API.
(function () {
    const btn = document.getElementById('googleBtn');
    const box = document.getElementById('ssoMsg');
    if (!btn) return;

    function show(text, type) {
        box.className = 'alert alert-' + type;
        box.textContent = text;
        box.style.display = 'block';
    }

    btn.addEventListener('click', async function () {
        if (typeof firebase === 'undefined') { show('Google sign-in could not load. Check your internet connection.', 'danger'); return; }
        if (!firebaseConfig.apiKey || firebaseConfig.apiKey.indexOf('PASTE_') === 0) { show('Google sign-in is not configured yet.', 'warning'); return; }

        btn.disabled = true;
        show('Opening Google sign-in…', 'info');
        try {
            if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
            const provider = new firebase.auth.GoogleAuthProvider();
            provider.setCustomParameters({ prompt: 'select_account' });
            const result  = await firebase.auth().signInWithPopup(provider);
            const idToken = await result.user.getIdToken();

            const res  = await fetch('api/sso_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idToken: idToken })
            });
            const data = await res.json();
            await firebase.auth().signOut();   // our PHP session takes over from here

            if (data.success) { window.location.href = data.redirect; return; }
            show(data.message || 'Sign-in failed.', data.pending ? 'warning' : 'danger');
        } catch (e) {
            if (e && e.code === 'auth/popup-closed-by-user') show('Sign-in was cancelled.', 'warning');
            else show('Google sign-in failed: ' + (e.message || e), 'danger');
        }
        btn.disabled = false;
    });
})();