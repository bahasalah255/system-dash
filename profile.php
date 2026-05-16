<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'backend/config.php';

$db = get_db();
$stmt = $db->prepare('SELECT full_name, email FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$email_success = '';
$email_error   = '';
$pass_success  = '';
$pass_error    = '';

// --- Handle form submissions via POST with a hidden action field ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_email') {
        $new_email = trim($_POST['new_email'] ?? '');
        $password  = $_POST['password'] ?? '';

        if (empty($new_email) || empty($password)) {
            $email_error = 'All fields are required.';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $email_error = 'Invalid email address.';
        } else {
            $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();

            if (!password_verify($password, $row['password'])) {
                $email_error = 'Incorrect password.';
            } else {
                $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
                $stmt->execute([$new_email, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $email_error = 'This email is already in use.';
                } else {
                    $stmt = $db->prepare('UPDATE users SET email = ? WHERE id = ?');
                    $stmt->execute([$new_email, $_SESSION['user_id']]);
                    $_SESSION['user_email'] = $new_email;
                    $user['email'] = $new_email;
                    $email_success = 'Email updated successfully.';
                }
            }
        }
    }

    if ($action === 'update_password') {
        $current_password  = $_POST['current_password'] ?? '';
        $new_password      = $_POST['new_password'] ?? '';
        $confirm_password  = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $pass_error = 'All fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $pass_error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 8) {
            $pass_error = 'New password must be at least 8 characters.';
        } else {
            $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();

            if (!password_verify($current_password, $row['password'])) {
                $pass_error = 'Current password is incorrect.';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hashed, $_SESSION['user_id']]);
                $pass_success = 'Password updated successfully.';
            }
        }
    }
}
?>
<?php include 'navbar.php'; ?>

<h1 style="font-size: 1.6rem; margin-bottom: 6px; color: #1a1a2e;">My Profile</h1>
<p style="color: #666; font-size: 0.9rem; margin-bottom: 32px;">
    <?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?> &mdash;
    <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
</p>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    <!-- Form 1: Change Email -->
    <div style="background: #fff; border: 1px solid #dde1e7; border-radius: 6px; padding: 24px;">
        <h2 style="font-size: 1rem; margin-bottom: 20px; color: #1a1a2e; border-bottom: 1px solid #eee; padding-bottom: 12px;">Change Email</h2>

        <?php if ($email_success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($email_success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($email_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($email_error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <input type="hidden" name="action" value="update_email">

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #444;">New Email</label>
                <input
                    type="email"
                    name="new_email"
                    required
                    style="width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem; outline: none;"
                    placeholder="new@email.com"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #444;">Confirm Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    style="width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem; outline: none;"
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit" style="background: #e94560; color: #fff; border: none; padding: 9px 20px; border-radius: 4px; font-size: 0.9rem; cursor: pointer; font-weight: 600;">
                Update Email
            </button>
        </form>
    </div>

    <!-- Form 2: Change Password -->
    <div style="background: #fff; border: 1px solid #dde1e7; border-radius: 6px; padding: 24px;">
        <h2 style="font-size: 1rem; margin-bottom: 20px; color: #1a1a2e; border-bottom: 1px solid #eee; padding-bottom: 12px;">Change Password</h2>

        <?php if ($pass_success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($pass_success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($pass_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($pass_error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <input type="hidden" name="action" value="update_password">

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #444;">Current Password</label>
                <input
                    type="password"
                    name="current_password"
                    required
                    style="width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem; outline: none;"
                    placeholder="Current password"
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #444;">New Password</label>
                <input
                    type="password"
                    name="new_password"
                    required
                    style="width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem; outline: none;"
                    placeholder="Min. 8 characters"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #444;">Confirm New Password</label>
                <input
                    type="password"
                    name="confirm_password"
                    required
                    style="width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.9rem; outline: none;"
                    placeholder="Repeat new password"
                >
            </div>

            <button type="submit" style="background: #1a1a2e; color: #fff; border: none; padding: 9px 20px; border-radius: 4px; font-size: 0.9rem; cursor: pointer; font-weight: 600;">
                Update Password
            </button>
        </form>
    </div>

</div>

</div>
</body>
</html>
