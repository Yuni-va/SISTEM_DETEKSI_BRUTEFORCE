<?php
$eventIcons = [
    "danger" => "bi bi-x-circle-fill",
    "warning" => "bi bi-exclamation-triangle-fill",
    "success" => "bi bi-check-circle-fill",
    "primary" => "bi bi-info-circle-fill"
];
?>

<article class="soc-card recent-events-panel">
    <div class="soc-card-header">
        <div>
            <span class="soc-eyebrow">Aktivitas Terbaru</span>
            <h5>Recent Security Events</h5>
        </div>
        <a class="soc-link" href="activity_logs.php">Lihat Semua</a>
    </div>

    <div class="timeline-list">
        <?php if(mysqli_num_rows($recentEvents) === 0): ?>
            <div class="empty-state">Belum ada aktivitas keamanan.</div>
        <?php endif; ?>

        <?php while($event = mysqli_fetch_assoc($recentEvents)): ?>
            <?php
                $severity = $event['severity'] ?: 'primary';
                $icon = $eventIcons[$severity] ?? $eventIcons['primary'];
            ?>
            <div class="timeline-item">
                <div class="timeline-icon event-<?= htmlspecialchars($severity) ?>">
                    <i class="<?= htmlspecialchars($icon) ?>"></i>
                </div>
                <div class="timeline-body">
                    <div class="timeline-main">
                        <strong><?= htmlspecialchars($event['title']) ?></strong>
                        <time><?= date('H:i:s', strtotime($event['event_time'])) ?></time>
                    </div>
                    <small><?= htmlspecialchars($event['description']) ?></small>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</article>
