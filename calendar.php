<?php
include __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$year  = isset($_GET['y']) ? (int)$_GET['y'] : date('Y');
$month = isset($_GET['m']) ? (int)$_GET['m'] : date('n');

$firstDayOfMonth = new DateTime("$year-$month-01");
$daysInMonth     = (int)$firstDayOfMonth->format('t');
$startDayOfWeek  = (int)$firstDayOfMonth->format('N'); // 1 (Mon) → 7 (Sun)

$today = date('Y-m-d');

/* Fetch events for this month */
$stmt = $pdo->prepare("SELECT * FROM events WHERE YEAR(event_date) = ? AND MONTH(event_date) = ? ORDER BY event_date");
$stmt->execute([$year, $month]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Index events by date */
$eventsByDate = [];
foreach ($events as $event) {
    $eventsByDate[$event['event_date']][] = $event;
}

/* Prev / Next month */
$prev = (clone $firstDayOfMonth)->modify('-1 month');
$next = (clone $firstDayOfMonth)->modify('+1 month');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sedibeng Jukskei * Calendar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
<style>
/* Calendar layout */
.calendar-layout {
    display: grid;
    grid-template-columns: 3fr 1.2fr;
    gap: 20px;
}

.calendar-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-weight: bold;
}
.calendar-nav a {
    color: #022359;
    text-decoration: none;
}
.calendar-nav a:hover {
    text-decoration: underline;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #022359;
    color: white;
    text-align: center;
    font-weight: bold;
    padding: 10px 0;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.calendar-day {
    min-height: 110px;
    border: 1px solid #ccc;
    padding: 5px;
    font-size: 0.9em;
    background: white;
}
.calendar-day.today {
    border: 2px solid #022359;
    background: #eef3ff;
}
.calendar-day .date {
    font-weight: bold;
    margin-bottom: 5px;
}
.calendar-event {
    background: #022359;
    color: white;
    padding: 2px 5px;
    margin-top: 3px;
    font-size: 0.8em;
    border-radius: 3px;
}

/* Event list sidebar */
.event-list {
    border-left: 2px solid #ccc;
    padding-left: 15px;
}
.event-item {
    margin-bottom: 10px;
}
.event-item strong {
    color: #022359;
}

/* MOBILE STYLES */
@media (max-width: 768px) {
    .calendar-layout {
        display: block;
    }
    .calendar-grid {
        grid-template-columns: repeat(7, 1fr);
        overflow-x: auto; /* allow horizontal scroll if needed */
    }
    .calendar-day {
        min-height: 80px;
        font-size: 0.8em;
    }
    .event-list {
        border-left: none;
        border-top: 2px solid #ccc;
        margin-top: 20px;
        padding-left: 0;
        padding-top: 10px;
    }
    .calendar-header {
        display: none; /* optional: hide weekday labels for very small screens */
    }
}
</style>
</head>
<body>
<?php include BASE_PATH . '/includes/header.php'; ?>

<h1 class="auth_h2_class"><?= $firstDayOfMonth->format('F Y') ?></h1>

<div class="calendar-layout">
    <!-- CALENDAR -->
    <div>
        <div class="calendar-nav">
            <a href="?y=<?= $prev->format('Y') ?>&m=<?= $prev->format('n') ?>">&laquo; Prev</a>
            <strong><?= $firstDayOfMonth->format('F Y') ?></strong>
            <a href="?y=<?= $next->format('Y') ?>&m=<?= $next->format('n') ?>">Next &raquo;</a>
        </div>

        <div class="calendar-header">
            <div>Mon</div><div>Tue</div><div>Wed</div>
            <div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
        </div>

        <div class="calendar-grid">
            <?php for ($i = 1; $i < $startDayOfWeek; $i++): ?>
                <div class="calendar-day"></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = ($date === $today);
            ?>
            <div class="calendar-day <?= $isToday ? 'today' : '' ?>">
                <div class="date"><?= $day ?></div>
                <?php if (!empty($eventsByDate[$date])): ?>
                    <?php foreach ($eventsByDate[$date] as $event): ?>
                        <div class="calendar-event">
                            <?= htmlspecialchars($event['title']) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- EVENT LIST -->
    <aside class="event-list">
        <h3>Events this month</h3>

        <?php if (!$events): ?>
            <p>No events scheduled.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="event-item">
                    <strong><?= date('j M', strtotime($event['event_date'])) ?></strong><br>
                    <?= htmlspecialchars($event['title']) ?><br>
                    <small><?= htmlspecialchars($event['description'] ?? '') ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </aside>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
</body>
</html>
