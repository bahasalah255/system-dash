<?php
require_once 'backend/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: frontend/pages/dashboard-' . $_SESSION['user_role'] . '.html');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']          ?? '';
    $confirm   = $_POST['confirm_password']  ?? '';

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $db->prepare(
                'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$full_name, $email, $hashed, 'student']);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Academic Events</title>
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

        .card-header {
            margin-bottom: 28px;
        }

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
        }

        .field {
            margin-bottom: 16px;
        }

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

        .strength-bar {
            height: 3px;
            background: #e3e3e1;
            border-radius: 3px;
            margin-top: 6px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0;
            border-radius: 3px;
            transition: width 0.25s, background 0.25s;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }

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
            margin-top: 4px;
        }

        .btn:hover { background: #0c5f58; }

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
        <h1>Create an account</h1>
        <p>Join the Academic Events portal as a student.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="field">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" required autocomplete="name"
                   placeholder="Jane Doe"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required autocomplete="email"
                   placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   placeholder="Minimum 8 characters">
            <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
        </div>

        <div class="field">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                   autocomplete="new-password" placeholder="Repeat password">
        </div>

        <button type="submit" class="btn">Create account</button>
    </form>

    <div class="footer-link">
        Already have an account? <a href="login.php">Sign in</a>
    </div>

    <div style="margin-top: 12px; text-align: center;">
        <a href="index.php" style="font-size: 12.5px; color: #9ca3af; text-decoration: none;">
            &larr; Back to home
        </a>
    </div>
</div>

<script>
    const pw   = document.getElementById('password');
    const fill = document.getElementById('strength-fill');
    pw.addEventListener('input', () => {
        const n = pw.value.length;
        if (n === 0)      { fill.style.width = '0';    fill.style.background = ''; }
        else if (n < 6)   { fill.style.width = '25%';  fill.style.background = '#ef4444'; }
        else if (n < 8)   { fill.style.width = '50%';  fill.style.background = '#f97316'; }
        else if (n < 12)  { fill.style.width = '75%';  fill.style.background = '#ca8a04'; }
        else              { fill.style.width = '100%'; fill.style.background = '#0f766e'; }
    });
</script>
</body>
</html>
