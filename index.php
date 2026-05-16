<?php

require_once 'backend/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_role    = $_SESSION['user_role'] ?? null;

$events = [];
try {
    $dsn  = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo  = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $stmt = $pdo->prepare(
        'SELECT title, event_date, location
         FROM events
         WHERE event_date >= CURDATE()
         ORDER BY event_date ASC
         LIMIT 3'
    );
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Events — University Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        /* =====================================================
           RESET & TOKENS
        ===================================================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent    : #0f766e;
            --accent-dk : #0c5f58;
            --dark      : #111110;
            --dark-2    : #1c1c1a;
            --text      : #1a1a18;
            --sub       : #52524f;
            --muted     : #8a8a87;
            --border    : #e3e3e1;
            --bg        : #f6f6f5;
            --white     : #ffffff;
            --max-w     : 1080px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            color: var(--text);
            background: var(--white);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        /* =====================================================
           LAYOUT HELPERS
        ===================================================== */
        .container {
            max-width: var(--max-w);
            margin: 0 auto;
            padding: 0 28px;
        }

        /* =====================================================
           NAVBAR
        ===================================================== */
        .site-nav {
            background: var(--dark);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #222220;
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }

        .nav-brand {
            font-size: 15px;
            font-weight: 700;
            color: #f5f5f4;
            letter-spacing: -0.2px;
        }

        .nav-brand span {
            color: var(--accent);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2px;
            list-style: none;
        }

        .nav-links a {
            display: block;
            padding: 6px 14px;
            font-size: 13.5px;
            font-weight: 500;
            color: #a8a8a4;
            border-radius: 5px;
            transition: background 0.12s, color 0.12s;
        }

        .nav-links a:hover { background: #1c1c1a; color: #f0f0ee; }

        .nav-links a.btn-nav {
            background: var(--accent);
            color: #fff;
            margin-left: 6px;
        }

        .nav-links a.btn-nav:hover { background: var(--accent-dk); }

        /* =====================================================
           HERO
        ===================================================== */
        .hero {
            background: var(--dark-2);
            padding: 96px 0 88px;
            text-align: center;
            border-bottom: 1px solid #2a2a28;
        }

        .hero-label {
            display: inline-block;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: clamp(32px, 5vw, 52px);
            font-weight: 700;
            color: #f5f5f4;
            letter-spacing: -1.5px;
            line-height: 1.1;
            max-width: 700px;
            margin: 0 auto 18px;
        }

        .hero p {
            font-size: 17px;
            color: #8a8a87;
            max-width: 520px;
            margin: 0 auto 36px;
            line-height: 1.65;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-block;
            padding: 11px 26px;
            background: var(--accent);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            letter-spacing: 0.1px;
            transition: background 0.12s;
        }

        .btn-primary:hover { background: var(--accent-dk); }

        .btn-outline {
            display: inline-block;
            padding: 11px 26px;
            background: transparent;
            color: #c8c8c4;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            border: 1px solid #3a3a38;
            transition: background 0.12s, border-color 0.12s, color 0.12s;
        }

        .btn-outline:hover {
            background: #252523;
            border-color: #4a4a48;
            color: #f0f0ee;
        }

        /* =====================================================
           SECTION SHARED
        ===================================================== */
        .section {
            padding: 80px 0;
        }

        .section-alt { background: var(--bg); }

        .section-tag {
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.6px;
            line-height: 1.2;
        }

        .section-sub {
            font-size: 15px;
            color: var(--sub);
            margin-top: 10px;
            max-width: 520px;
            line-height: 1.65;
        }

        /* =====================================================
           ABOUT
        ===================================================== */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 64px;
            align-items: start;
        }

        .about-text .section-sub {
            max-width: 100%;
            margin-top: 16px;
        }

        .about-text p + p {
            margin-top: 14px;
        }

        .about-box {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .about-box-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            background: #fafaf9;
        }

        .about-box-header h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.1px;
        }

        .about-box-rows { padding: 4px 0; }

        .about-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 13px 22px;
            border-bottom: 1px solid #f0f0ee;
        }

        .about-row:last-child { border-bottom: none; }

        .about-row-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            flex-shrink: 0;
            margin-top: 8px;
        }

        .about-row-text {
            font-size: 13.5px;
            color: var(--sub);
            line-height: 1.55;
        }

        .about-row-text strong {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1px;
        }

        /* =====================================================
           FEATURES
        ===================================================== */
        .features-header {
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .feature-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 28px 26px;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: var(--white);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--accent);
        }

        .feature-icon svg {
            width: 22px;
            height: 22px;
            stroke-width: 1.75;
        }

        .feature-card h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.1px;
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 13.5px;
            color: var(--sub);
            line-height: 1.6;
        }

        /* =====================================================
           EVENTS PREVIEW
        ===================================================== */
        .events-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-link {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--accent);
            transition: color 0.1s;
        }

        .btn-link:hover { color: var(--accent-dk); }

        .events-list {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .event-row {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 18px 24px;
            border-bottom: 1px solid #f0f0ee;
            transition: background 0.1s;
        }

        .event-row:last-child { border-bottom: none; }
        .event-row:hover { background: #fafaf9; }

        .event-date-col {
            flex-shrink: 0;
            width: 52px;
            text-align: center;
        }

        .event-day {
            display: block;
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -1px;
            line-height: 1;
        }

        .event-month {
            display: block;
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 3px;
        }

        .event-vline {
            width: 1px;
            height: 36px;
            background: var(--border);
            flex-shrink: 0;
        }

        .event-info { flex: 1; min-width: 0; }

        .event-title-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.1px;
        }

        .event-location {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px;
        }

        .no-events {
            padding: 48px 24px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
        }

        /* =====================================================
           FOOTER
        ===================================================== */
        .site-footer {
            background: var(--dark);
            border-top: 1px solid #222220;
            padding: 48px 0 0;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap;
            padding-bottom: 32px;
        }

        .footer-brand strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #f5f5f4;
            margin-bottom: 6px;
        }

        .footer-brand p {
            font-size: 13px;
            color: #5a5a57;
            max-width: 240px;
            line-height: 1.55;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-links a {
            font-size: 13.5px;
            color: #737370;
            font-weight: 500;
            transition: color 0.1s;
        }

        .footer-links a:hover { color: #d4d4d0; }

        .footer-copy {
            border-top: 1px solid #1f1f1e;
            padding: 16px 28px;
            font-size: 12px;
            color: #5a5a57;
            text-align: center;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */
        @media (max-width: 860px) {
            .about-grid {
                grid-template-columns: 1fr;
                gap: 36px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .hero h1 { letter-spacing: -1px; }
        }

        @media (max-width: 600px) {
            .container { padding: 0 18px; }
            .section   { padding: 56px 0; }
            .hero      { padding: 64px 0 60px; }

            .event-row { gap: 16px; padding: 16px 18px; }

            .footer-inner { flex-direction: column; }

            .nav-links a { padding: 6px 10px; font-size: 13px; }
        }
    </style>
</head>
<body>

<!-- ================================================
     NAVBAR
================================================ -->
<header class="site-nav">
    <div class="container nav-inner">
        <a href="index.php" class="nav-brand">Academic<span>Events</span></a>
        <ul class="nav-links">
            <?php if ($is_logged_in && $user_role === 'student'): ?>
                <li><a href="events.php">Dashboard</a></li>
                <li><a href="backend/logout.php" class="btn-nav">Logout</a></li>
            <?php elseif ($is_logged_in && $user_role === 'manager'): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="backend/logout.php" class="btn-nav">Logout</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn-nav">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<!-- ================================================
     HERO
================================================ -->
<section class="hero">
    <div class="container">
        <span class="hero-label">University Portal</span>
        <h1>Discover Academic Events</h1>
        <p>Stay informed about workshops, seminars and conferences at your university. All in one place.</p>
        <div class="hero-actions">
            <a href="events.php" class="btn-primary">View Events</a>
            <a href="register.php" class="btn-outline">Create an Account</a>
        </div>
    </div>
</section>

<!-- ================================================
     ABOUT
================================================ -->
<section class="section section-alt">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <div class="section-tag">About</div>
                <h2 class="section-title">About this platform</h2>
                <p class="section-sub">
                    Academic Events is a central portal for students and staff at the university.
                    It brings together all academic gatherings — from small workshops to large seminars —
                    into a single, easy-to-navigate space.
                </p>
                <p class="section-sub" style="margin-top: 14px;">
                    Students can create a free account, browse all upcoming events, and manage their
                    profile from a personal dashboard. Managers can publish and remove events directly
                    from the administration panel.
                </p>
            </div>

            <div>
                <div class="about-box">
                    <div class="about-box-header">
                        <h4>Platform at a glance</h4>
                    </div>
                    <div class="about-box-rows">
                        <div class="about-row">
                            <div class="about-row-dot"></div>
                            <div class="about-row-text">
                                <strong>Open to all students</strong>
                                Any enrolled student can register and access the portal for free.
                            </div>
                        </div>
                        <div class="about-row">
                            <div class="about-row-dot"></div>
                            <div class="about-row-text">
                                <strong>Updated continuously</strong>
                                Events are added and managed by university staff in real time.
                            </div>
                        </div>
                        <div class="about-row">
                            <div class="about-row-dot"></div>
                            <div class="about-row-text">
                                <strong>Secure by design</strong>
                                Accounts are protected with hashed passwords and session-based authentication.
                            </div>
                        </div>
                        <div class="about-row">
                            <div class="about-row-dot"></div>
                            <div class="about-row-text">
                                <strong>No installation needed</strong>
                                Fully web-based — access from any device with a browser.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================================================
     FEATURES
================================================ -->
<section class="section">
    <div class="container">
        <div class="features-header">
            <div class="section-tag">Features</div>
            <h2 class="section-title">What you can do</h2>
        </div>

        <div class="features-grid">

            <div class="feature-card">
                <div class="feature-icon">
                    <!-- Tabler: calendar-event -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="5" width="16" height="16" rx="2"/>
                        <line x1="16" y1="3" x2="16" y2="7"/>
                        <line x1="8" y1="3" x2="8" y2="7"/>
                        <line x1="4" y1="11" x2="20" y2="11"/>
                        <rect x="8" y="15" width="2" height="2"/>
                    </svg>
                </div>
                <h3>Browse events</h3>
                <p>View all upcoming academic events sorted by date. Filter by type, location and more from your student dashboard.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <!-- Tabler: user-plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
                        <line x1="19" y1="11" x2="19" y2="17"/>
                        <line x1="16" y1="14" x2="22" y2="14"/>
                    </svg>
                </div>
                <h3>Create an account</h3>
                <p>Register as a student in seconds. Access your personal dashboard to manage your profile and credentials.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <!-- Tabler: bell -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3H4a4 4 0 0 0 2-3v-3a7 7 0 0 1 4-6"/>
                        <path d="M9 17v1a3 3 0 0 0 6 0v-1"/>
                    </svg>
                </div>
                <h3>Stay updated</h3>
                <p>Never miss a workshop or seminar. The events list is always up to date with the latest additions from university staff.</p>
            </div>

        </div>
    </div>
</section>

<!-- ================================================
     UPCOMING EVENTS PREVIEW
================================================ -->
<section class="section section-alt">
    <div class="container">
        <div class="events-header">
            <div>
                <div class="section-tag">Schedule</div>
                <h2 class="section-title">Upcoming events</h2>
            </div>
            <a href="events.php" class="btn-link">View all events &rarr;</a>
        </div>

        <?php if (empty($events)): ?>
            <div class="events-list">
                <div class="no-events">No upcoming events at the moment. Check back soon.</div>
            </div>
        <?php else: ?>
            <div class="events-list">
                <?php foreach ($events as $ev):
                    $d     = new DateTime($ev['event_date']);
                    $day   = $d->format('d');
                    $month = strtoupper($d->format('M'));
                ?>
                <div class="event-row">
                    <div class="event-date-col">
                        <span class="event-day"><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="event-month"><?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="event-vline"></div>
                    <div class="event-info">
                        <div class="event-title-text"><?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if (!empty($ev['location'])): ?>
                            <div class="event-location"><?= htmlspecialchars($ev['location'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================================================
     FOOTER
================================================ -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <strong>AcademicEvents</strong>
                <p>A central portal for discovering and following academic events at the university.</p>
            </div>

            <ul class="footer-links">
                <?php if ($is_logged_in && $user_role === 'student'): ?>
                    <li><a href="events.php">Dashboard</a></li>
                    <li><a href="backend/logout.php">Logout</a></li>
                <?php elseif ($is_logged_in && $user_role === 'manager'): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="backend/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="footer-copy">
        &copy; <?= date('Y') ?> Academic Events Portal. All rights reserved.
    </div>
</footer>

</body>
</html>
