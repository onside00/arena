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
        <div class="top-header">
            <a class="telegram-btn telegram-btn-top"
               href="https://t.me/+9Akzb5efjaNlMjA8"
               target="_blank"
               rel="noopener noreferrer nofollow"
               aria-label="الانضمام إلى قناة أرينا على تليجرام">
                <span class="telegram-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true">
                        <path d="M21.78 3.46a1.55 1.55 0 0 0-1.63-.22L3.16 9.8c-1.1.43-1.08 1.04-.2 1.31l4.36 1.36 1.68 5.2c.2.56.1.78.7.78.46 0 .66-.21.91-.45l2.1-2.04 4.36 3.22c.8.44 1.38.21 1.58-.74l2.7-12.73c.28-1.13-.43-1.64-1.57-1.25ZM8 12.15l9.86-6.22c.49-.3.94-.14.57.2l-8.14 7.35-.32 3.42L8 12.15Z"/>
                    </svg>
                </span>
                <span>قناة التليجرام</span>
            </a>

            <div class="top-header-title">أرينا لايف</div>
        </div>

        <div class="brand-copy">
            <div class="brand-logo-circle">
                <img class="brand-logo-large" src="assets/img/arena4k-logo.png" alt="Arena 4K">
            </div>
            <h1 id="site-title"><?= e(SITE_NAME) ?></h1>
            <p>اختر المباراة واضغط على البطاقة للمتابعة</p>
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
