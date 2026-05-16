<?php
require_once 'backend/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role      = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$today     = date('Y-m-d');

// Fetch all upcoming events directly — same query logic as backend/events.php
$db   = get_db();
$stmt = $db->prepare(
    'SELECT id, title, description, event_date, location
     FROM events
     WHERE event_date >= ?
     ORDER BY event_date ASC'
);
$stmt->execute([$today]);
$events = $stmt->fetchAll();

// Dashboard URL depends on role
$dashboard_url = ($role === 'manager')
    ? 'frontend/pages/dashboard-manager.html'
    : 'frontend/pages/dashboard-student.html';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events — Academic Events</title>
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
            --accent-light:  #f0fdfa;
            --dark:          #111110;
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

        .nav-links a.btn-accent:hover { background: #0c5f58; }

        .nav-user {
            font-size: 12px;
            color: #5a5a57;
        }

        /* ---- Layout ---- */
        .page-wrap {
            max-width: 820px;
            margin: 0 auto;
            padding: 32px 24px 64px;
        }

        /* ---- Page header ---- */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .page-title {
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
        }

        /* ---- Search ---- */
        .search-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .search-wrap svg {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--hint);
            pointer-events: none;
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .search-wrap input {
            padding: 6px 10px 6px 30px;
            border: 0.5px solid var(--border-strong);
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            background: var(--surface);
            color: var(--text);
            width: 200px;
            transition: border-color 0.15s, width 0.2s;
        }

        .search-wrap input:focus { border-color: var(--accent); width: 240px; }
        .search-wrap input::placeholder { color: var(--hint); }

        /* ---- Events list ---- */
        .events-list {
            display: flex;
            flex-direction: column;
            gap: 1px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .event-item {
            background: var(--surface);
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 18px 22px;
            transition: background 0.1s;
        }

        .event-item:hover { background: #fafaf9; }

        .event-date-block {
            flex-shrink: 0;
            width: 42px;
            text-align: center;
            padding-top: 2px;
        }

        .event-day {
            display: block;
            font-size: 22px;
            font-weight: 500;
            color: var(--text);
            line-height: 1;
            letter-spacing: -1px;
        }

        .event-month {
            display: block;
            font-size: 10px;
            font-weight: 400;
            color: var(--hint);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 3px;
        }

        .event-divider {
            width: 0.5px;
            align-self: stretch;
            background: var(--border-strong);
            flex-shrink: 0;
        }

        .event-body { flex: 1; min-width: 0; }

        .event-title {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text);
            line-height: 1.4;
        }

        .event-location {
            font-size: 12px;
            color: var(--hint);
            margin-top: 3px;
        }

        .event-desc {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 6px;
            line-height: 1.55;
        }

        /* ---- Empty state ---- */
        .events-empty {
            background: var(--surface);
            text-align: center;
            padding: 56px 24px;
            color: var(--hint);
            font-size: 13px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .events-empty strong {
            display: block;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--muted);
            margin-bottom: 4px;
        }

        /* ---- No results (search) ---- */
        .no-results {
            display: none;
            text-align: center;
            padding: 32px;
            color: var(--hint);
            font-size: 13px;
            background: var(--surface);
            border-radius: 0 0 8px 8px;
        }

        @media (max-width: 600px) {
            .page-wrap { padding: 20px 16px 48px; }
            .navbar { padding: 0 16px; }
            .event-item { gap: 14px; padding: 16px; }
            .search-wrap input { width: 160px; }
            .search-wrap input:focus { width: 180px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="nav-brand">Academic Events</a>
    <ul class="nav-links">
        <li><a href="events-list.php" class="active">Events</a></li>
        <?php if ($role === 'manager'): ?>
            <li><a href="add-event.php">Add event</a></li>
        <?php endif; ?>
        <li><a href="<?= htmlspecialchars($dashboard_url, ENT_QUOTES, 'UTF-8') ?>">Dashboard</a></li>
        <li><a href="backend/logout.php" class="btn-accent">Logout</a></li>
    </ul>
    <span class="nav-user"><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></span>
</nav>

<!-- Content -->
<div class="page-wrap">

    <div class="page-header">
        <span class="page-title">Upcoming events</span>
        <?php if (!empty($events)): ?>
        <div class="search-wrap">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" id="search-input" placeholder="Search events...">
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($events)): ?>
        <div class="events-empty">
            <strong>No upcoming events</strong>
            Check back later — events will appear here once scheduled.
        </div>
    <?php else: ?>
        <div class="events-list" id="events-list">
            <?php foreach ($events as $ev):
                $d     = new DateTime($ev['event_date']);
                $day   = $d->format('d');
                $month = strtoupper($d->format('M'));
            ?>
            <div class="event-item" data-searchable="<?= htmlspecialchars(
                $ev['title'] . ' ' . $ev['location'] . ' ' . $ev['description'],
                ENT_QUOTES, 'UTF-8'
            ) ?>">
                <div class="event-date-block">
                    <span class="event-day"><?= $day ?></span>
                    <span class="event-month"><?= $month ?></span>
                </div>
                <div class="event-divider"></div>
                <div class="event-body">
                    <div class="event-title"><?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($ev['location'])): ?>
                        <div class="event-location"><?= htmlspecialchars($ev['location'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($ev['description'])): ?>
                        <p class="event-desc"><?= htmlspecialchars($ev['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="no-results" id="no-results">No events match your search.</div>
    <?php endif; ?>

</div>

<script>
    const input   = document.getElementById('search-input');
    const noRes   = document.getElementById('no-results');
    if (input) {
        input.addEventListener('input', function () {
            const q     = this.value.trim().toLowerCase();
            const items = document.querySelectorAll('#events-list .event-item');
            let visible = 0;
            items.forEach(item => {
                const match = item.dataset.searchable.toLowerCase().includes(q);
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            noRes.style.display = visible === 0 ? 'block' : 'none';
        });
    }
</script>
</body>
</html>
