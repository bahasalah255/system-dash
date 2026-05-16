<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Academic Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f6f6f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            background: #fff;
            border: 1px solid #e3e3e1;
            border-radius: 8px;
            padding: 36px 32px;
            width: 100%;
            max-width: 400px;
        }

        .card-header { margin-bottom: 24px; }

        .card-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a18;
            letter-spacing: -0.3px;
        }

        .card-header p {
            font-size: 13.5px;
            color: #8a8a87;
            margin-top: 4px;
            line-height: 1.5;
        }

        .steps {
            display: flex;
            gap: 6px;
            margin-bottom: 24px;
        }

        .step {
            flex: 1;
            height: 3px;
            border-radius: 3px;
            background: #e3e3e1;
            transition: background 0.25s;
        }

        .step.active { background: #0f766e; }

        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #52524f;
            margin-bottom: 6px;
        }

        .field input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e3e3e1;
            border-radius: 6px;
            font-size: 13.5px;
            font-family: inherit;
            color: #1a1a18;
            background: #fafaf9;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }

        .field input:focus {
            border-color: #0f766e;
            background: #fff;
        }

        .field .hint {
            font-size: 11.5px;
            color: #a8a8a4;
            margin-top: 4px;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 18px;
            display: none;
        }

        .alert.error   { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert.success { background: #f0fdf9; border: 1px solid #99e6d8; color: #0f766e; }

        .btn {
            width: 100%;
            padding: 10px;
            background: #0f766e;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.12s;
        }

        .btn:hover { background: #0c5f58; }
        .btn:disabled { background: #8a8a87; cursor: not-allowed; }

        .step-block { display: none; }
        .step-block.active { display: block; }

        .footer-link {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #8a8a87;
        }

        .footer-link a {
            color: #0f766e;
            font-weight: 500;
            text-decoration: none;
        }

        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>Reset password</h1>
        <p id="subtitle">Enter your email and we will issue a reset token.</p>
    </div>

    <div class="steps">
        <div class="step active" id="bar-1"></div>
        <div class="step"        id="bar-2"></div>
    </div>

    <div class="alert" id="msg"></div>

    <!-- Step 1 -->
    <div class="step-block active" id="step-1">
        <form id="form-verify">
            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" required autocomplete="email" placeholder="you@example.com">
            </div>
            <button type="submit" class="btn" id="btn-verify">Send reset token</button>
        </form>
    </div>

    <!-- Step 2 -->
    <div class="step-block" id="step-2">
        <form id="form-reset">
            <div class="field">
                <label for="token">Reset token</label>
                <input type="text" id="token" required autocomplete="off" placeholder="Paste your token">
                <p class="hint">Check your email — or use the token shown in the response during development.</p>
            </div>
            <div class="field">
                <label for="new_password">New password</label>
                <input type="password" id="new_password" required autocomplete="new-password" placeholder="Minimum 8 characters">
            </div>
            <div class="field">
                <label for="confirm_password">Confirm new password</label>
                <input type="password" id="confirm_password" required autocomplete="new-password" placeholder="Repeat password">
            </div>
            <button type="submit" class="btn" id="btn-reset">Reset password</button>
        </form>
    </div>

    <div class="footer-link"><a href="login.php">Back to sign in</a></div>
</div>

<script>
    const ENDPOINT = 'backend/reset_password.php';
    let verifiedEmail = '';

    function showMsg(type, text) {
        const el = document.getElementById('msg');
        el.className = 'alert ' + type;
        el.textContent = text;
        el.style.display = 'block';
    }

    function goStep2() {
        document.getElementById('step-1').classList.remove('active');
        document.getElementById('step-2').classList.add('active');
        document.getElementById('bar-2').classList.add('active');
        document.getElementById('subtitle').textContent = 'Enter the token and choose a new password.';
        document.getElementById('msg').style.display = 'none';
    }

    document.getElementById('form-verify').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value.trim();
        if (!email) { showMsg('error', 'Please enter your email address.'); return; }

        const btn = document.getElementById('btn-verify');
        btn.disabled = true; btn.textContent = 'Checking...';

        const body = new FormData();
        body.append('action', 'verify');
        body.append('email', email);

        try {
            const res  = await fetch(ENDPOINT, { method: 'POST', body, credentials: 'include' });
            const data = await res.json();
            if (data.success) {
                verifiedEmail = email;
                const note = data.data?.token ? ' Token (dev): ' + data.data.token : '';
                showMsg('success', data.message + note);
                setTimeout(goStep2, 1800);
            } else {
                showMsg('error', data.message);
            }
        } catch { showMsg('error', 'Network error. Please try again.'); }
        finally  { btn.disabled = false; btn.textContent = 'Send reset token'; }
    });

    document.getElementById('form-reset').addEventListener('submit', async (e) => {
        e.preventDefault();
        const token   = document.getElementById('token').value.trim();
        const newPw   = document.getElementById('new_password').value;
        const confPw  = document.getElementById('confirm_password').value;
        const btn     = document.getElementById('btn-reset');

        if (!token || !newPw || !confPw) { showMsg('error', 'All fields are required.'); return; }
        if (newPw.length < 8)           { showMsg('error', 'Password must be at least 8 characters.'); return; }
        if (newPw !== confPw)           { showMsg('error', 'Passwords do not match.'); return; }

        btn.disabled = true; btn.textContent = 'Resetting...';

        const body = new FormData();
        body.append('action', 'reset');
        body.append('email', verifiedEmail);
        body.append('token', token);
        body.append('new_password', newPw);

        try {
            const res  = await fetch(ENDPOINT, { method: 'POST', body, credentials: 'include' });
            const data = await res.json();
            if (data.success) {
                showMsg('success', data.message + ' Redirecting...');
                setTimeout(() => { window.location.href = 'login.php'; }, 1800);
            } else {
                showMsg('error', data.message);
            }
        } catch { showMsg('error', 'Network error. Please try again.'); }
        finally  { btn.disabled = false; btn.textContent = 'Reset password'; }
    });
</script>
</body>
</html>
