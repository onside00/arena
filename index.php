<?php
declare(strict_types=1);
require __DIR__ . '/security.php';
arena_enforce_public_access();
require __DIR__ . '/config.php';

$sql = "
    SELECT id, team1_name, team1_logo, team2_name, team2_logo
    FROM matches
    WHERE status = 'active'
      AND match_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY display_order ASC, match_time ASC, id ASC
";
$result = $db->query($sql);
$matches = $result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="theme-color" content="#0a1530">
    <title><?= e(SITE_NAME) ?></title>
    <meta name="description" content="أرينا 4K - مباريات اليوم">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="public-body">
<main class="public-shell">
    <section class="brand-hero" aria-labelledby="site-title">
        <img class="brand-logo-large" src="assets/img/arena4k-logo.png" alt="Arena 4K">
        <div class="brand-copy">
            <p class="brand-overline">ARENA 4K</p>
            <h1 id="site-title"><?= e(SITE_NAME) ?></h1>
            <p>اختر المباراة واضغط على البطاقة للمتابعة</p>
            <a class="telegram-btn" href="https://t.me/+9Akzb5efjaNlMjA8" target="_blank" rel="noopener noreferrer nofollow" aria-label="قناة أرينا 4K على تليجرام">
                <span class="telegram-icon" aria-hidden="true">✈</span>
                <span>قناة التليجرام</span>
            </a>
        </div>
    </section>

    <section class="matches-section" aria-label="المباريات">
        <?php if (!$matches): ?>
            <div class="empty-state">
                <span class="empty-ball">⚽</span>
                <h2>لا توجد مباريات حالياً</h2>
                <p>ستظهر المباريات هنا عند إضافتها.</p>
            </div>
        <?php else: ?>
            <div class="matches-list">
                <?php foreach ($matches as $match): ?>
                    <a class="match-card" href="go.php?id=<?= (int)$match['id'] ?>" rel="nofollow">
                        <div class="team-block">
                            <div class="team-logo-wrap">
                                <?php if (!empty($match['team1_logo'])): ?>
                                    <img class="team-logo" src="uploads/teams/<?= e(safe_logo_name($match['team1_logo'])) ?>" alt="<?= e($match['team1_name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="team-logo-fallback"><?= e(mb_substr($match['team1_name'], 0, 1, 'UTF-8')) ?></span>
                                <?php endif; ?>
                            </div>
                            <strong><?= e($match['team1_name']) ?></strong>
                        </div>

                        <span class="versus-dot" aria-hidden="true">VS</span>

                        <div class="team-block">
                            <div class="team-logo-wrap">
                                <?php if (!empty($match['team2_logo'])): ?>
                                    <img class="team-logo" src="uploads/teams/<?= e(safe_logo_name($match['team2_logo'])) ?>" alt="<?= e($match['team2_name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="team-logo-fallback"><?= e(mb_substr($match['team2_name'], 0, 1, 'UTF-8')) ?></span>
                                <?php endif; ?>
                            </div>
                            <strong><?= e($match['team2_name']) ?></strong>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <footer class="public-footer">© <?= date('Y') ?> <?= e(SITE_NAME) ?></footer>
</main>
<script src="assets/js/app.js"></script>
<script src="assets/js/guard.js"></script>
</body>
</html>
