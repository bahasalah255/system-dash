<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'student') {
        header('Location: events.php');
    } else {
        header('Location: manager/dashboard.php');
    }
    exit;
}

require_once 'backend/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $db = get_db();
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

            if ($user['role'] === 'student') {
                header('Location: events.php');
            } else {
                header('Location: manager/dashboard.php');
            }
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
    <title>Login - Academic Events</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #fff;
            border: 1px solid #dde1e7;
            border-radius: 8px;
            padding: 40px 36px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        h1 {
            font-size: 1.4rem;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        p.sub {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 28px;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 6px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.9rem;
            outline: none;
            margin-bottom: 18px;
            transition: border 0.15s;
        }

        input:focus {
            border-color: #e94560;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #e94560;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        button:hover {
            background: #c73652;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 0.88rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Academic Events</h1>
    <p class="sub">Sign in to your account</p>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Password">

        <button type="submit">Sign In</button>
    </form>
</div>
</body>
</html>
