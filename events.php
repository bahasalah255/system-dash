<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'backend/config.php';

$db = get_db();
$stmt = $db->prepare('SELECT id, title, description, event_date, location FROM events ORDER BY event_date ASC');
$stmt->execute();
$events = $stmt->fetchAll();
?>
<?php include 'navbar.php'; ?>

<h1 style="font-size: 1.6rem; margin-bottom: 24px; color: #1a1a2e;">Upcoming Events</h1>

<?php if (empty($events)): ?>
    <p style="color: #666;">No events scheduled at the moment.</p>
<?php else: ?>
    <div style="display: grid; gap: 16px;">
        <?php foreach ($events as $event): ?>
            <div style="background: #fff; border: 1px solid #dde1e7; border-radius: 6px; padding: 20px 24px; border-left: 4px solid #e94560;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <h2 style="font-size: 1.1rem; color: #1a1a2e; font-weight: 600;">
                        <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <span style="white-space: nowrap; font-size: 0.85rem; color: #555; background: #f0f2f5; padding: 4px 10px; border-radius: 4px;">
                        <?= htmlspecialchars(date('d M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <?php if (!empty($event['description'])): ?>
                    <p style="margin-top: 10px; color: #555; line-height: 1.6; font-size: 0.9rem;">
                        <?= htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($event['location'])): ?>
                    <p style="margin-top: 10px; font-size: 0.85rem; color: #888;">
                        Location: <?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>
</body>
</html>
