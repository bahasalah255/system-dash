<?php
require_once 'backend/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $target = ($_SESSION['user_role'] === 'manager') ? 'dashboard.php' : 'events.php';
    header('Location: ' . $target);
    exit;
}

$error      = '';
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']         ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id, full_name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['user_name']  = $user['full_name'];

            $target = ($user['role'] === 'manager') ? 'dashboard.php' : 'events.php';
            header('Location: ' . $target);
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
    <title>Login — Academic Events</title>
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
            max-width: 380px;
        }

        .card-header { margin-bottom: 28px; }

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

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #f0fdf9;
            border: 1px solid #99e6d8;
            color: #0f766e;
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
        }

        .btn:hover { background: #0c5f58; }

        .footer-links {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #8a8a87;
        }

        .footer-links a {
            color: #0f766e;
            font-weight: 500;
            text-decoration: none;
        }

        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>Sign in</h1>
        <p>Academic Events Portal</p>
    </div>

    <?php if ($registered): ?>
        <div class="alert-success">Account created successfully. You can now sign in.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required autocomplete="email"
                   placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required
                   autocomplete="current-password" placeholder="Password">
        </div>
        <button type="submit" class="btn">Sign in</button>
    </form>

    <div class="footer-links">
        <a href="reset_password.php">Forgot password?</a>
        <a href="register.php">Create an account</a>
    </div>

    <div style="margin-top: 16px; text-align: center;">
        <a href="index.php" style="font-size: 12.5px; color: #9ca3af; text-decoration: none;">
            &larr; Back to home
        </a>
    </div>
</div>
</body>
</html>
