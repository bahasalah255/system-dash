<?php
require_once 'backend/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Manager only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$success   = '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Same validation logic as backend/events.php action=add
    $title       = trim($_POST['title']       ?? '');
    $event_date  = trim($_POST['event_date']  ?? '');
    $location    = trim($_POST['location']    ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $event_date === '') {
        $error = 'Title and date are required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        $error = 'Invalid date format.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare(
            'INSERT INTO events (title, description, event_date, location, created_by)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $description, $event_date, $location, $_SESSION['user_id']]);
        $success = 'Event "' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" added successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add event — Academic Events</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:            #f5f5f4;
            --surface:       #ffffff;
            --border:        rgba(0,0,0,0.08);
            --border-strong: rgba(0,0,0,0.14);
            --text:          #1a1a1a;
            --muted:         #6b7280;
            --hint:          #9ca3af;
            --accent:        #0f766e;
            --accent-dk:     #0c5f58;
            --dark:          #111110;
            --red:           #b91c1c;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: var(--text);
            background: var(--bg);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ---- Navbar ---- */
        .navbar {
            background: var(--dark);
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #222220;
        }

        .nav-brand {
            font-size: 14px;
            font-weight: 500;
            color: #f5f5f4;
            text-decoration: none;
            letter-spacing: -0.1px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2px;
            list-style: none;
        }

        .nav-links a {
            display: block;
            padding: 5px 12px;
            font-size: 13px;
            font-weight: 400;
            color: #a8a8a4;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.1s, color 0.1s;
        }

        .nav-links a:hover  { background: #1c1c1a; color: #f0f0ee; }
        .nav-links a.active { background: #1c1c1a; color: #f0f0ee; font-weight: 500; }

        .nav-links a.btn-accent {
            background: var(--accent);
            color: #fff;
            margin-left: 4px;
        }

        .nav-links a.btn-accent:hover { background: var(--accent-dk); }

        .nav-user { font-size: 12px; color: #5a5a57; }

        /* ---- Layout ---- */
        .page-wrap {
            max-width: 640px;
            margin: 0 auto;
            padding: 32px 24px 64px;
        }

        .page-title {
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 20px;
        }

        /* ---- Alerts ---- */
        .alert {
            padding: 11px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #f0fdfa;
            border: 0.5px solid rgba(15,118,110,0.25);
            color: var(--accent);
        }

        .alert-error {
            background: #fef2f2;
            border: 0.5px solid rgba(185,28,28,0.2);
            color: var(--red);
        }

        .alert-actions {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .alert-actions a {
            font-size: 12.5px;
            font-weight: 500;
            color: var(--accent);
            text-decoration: none;
        }

        .alert-actions a:hover { text-decoration: underline; }

        /* ---- Form card ---- */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .form-card-header {
            padding: 13px 20px;
            border-bottom: 0.5px solid var(--border);
            background: #fafaf9;
        }

        .form-card-header h2 {
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
        }

        .form-card-header p {
            font-size: 12px;
            color: var(--hint);
            margin-top: 1px;
        }

        .form-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field { display: flex; flex-direction: column; gap: 5px; }

        .field label {
            font-size: 12px;
            font-weight: 400;
            color: var(--muted);
        }

        .field input,
        .field textarea {
            padding: 8px 10px;
            border: 0.5px solid var(--border-strong);
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            color: var(--text);
            background: var(--bg);
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }

        .field input:focus,
        .field textarea:focus {
            border-color: var(--accent);
            background: var(--surface);
        }

        .field textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* ---- Form footer ---- */
        .form-footer {
            padding: 14px 20px;
            border-top: 0.5px solid var(--border);
            background: #fafaf9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit {
            padding: 7px 20px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 400;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.1s;
        }

        .btn-submit:hover { background: var(--accent-dk); }

        .btn-reset {
            padding: 7px 14px;
            background: transparent;
            color: var(--muted);
            border: 0.5px solid var(--border-strong);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 400;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.1s;
        }

        .btn-reset:hover { background: var(--bg); color: var(--text); }

        @media (max-width: 560px) {
            .page-wrap { padding: 20px 16px 48px; }
            .navbar { padding: 0 16px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="nav-brand">Academic Events</a>
    <ul class="nav-links">
        <li><a href="events-list.php">Events</a></li>
        <li><a href="add-event.php" class="active">Add event</a></li>
        <li><a href="frontend/pages/dashboard-manager.html">Dashboard</a></li>
        <li><a href="backend/logout.php" class="btn-accent">Logout</a></li>
    </ul>
    <span class="nav-user"><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></span>
</nav>

<!-- Content -->
<div class="page-wrap">

    <div class="page-title">Add a new event</div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= $success ?>
            <div class="alert-actions">
                <a href="add-event.php">Add another event</a>
                <a href="events-list.php">View all events</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h2>Event details</h2>
            <p>Fill in the information below to publish a new event.</p>
        </div>

        <form method="POST" action="add-event.php">
            <div class="form-body">

                <div class="form-row">
                    <div class="field">
                        <label for="title">Title <span style="color:#b91c1c;">*</span></label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            required
                            placeholder="e.g. Machine Learning Workshop"
                            value="<?= $success ? '' : htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                    <div class="field">
                        <label for="event_date">Date <span style="color:#b91c1c;">*</span></label>
                        <input
                            type="date"
                            id="event_date"
                            name="event_date"
                            required
                            min="<?= date('Y-m-d') ?>"
                            value="<?= $success ? '' : htmlspecialchars($_POST['event_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                </div>

                <div class="field">
                    <label for="location">Location</label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="e.g. CS Building, Room 3"
                        value="<?= $success ? '' : htmlspecialchars($_POST['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Optional — brief description of the event"
                    ><?= $success ? '' : htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

            </div>

            <div class="form-footer">
                <button type="submit" class="btn-submit">Save event</button>
                <button type="reset"  class="btn-reset">Clear</button>
            </div>
        </form>
    </div>

</div>
</body>
</html>
