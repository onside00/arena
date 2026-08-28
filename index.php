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
    <meta name="theme-color" content="#0f172a">

    <title><?= e(SITE_NAME) ?></title>
    <meta name="description" content="أرينا لايف - مباريات اليوم">

    <link rel="stylesheet" href="assets/css/style.css?v=20260828-4">
</head>

<body class="public-body">

<main class="public-shell">

    <header class="arena-main-header">

        <div class="arena-header-row">

            <div class="arena-header-side arena-header-side-right">
                <a
                    class="arena-telegram-btn"
                    href="https://t.me/+9Akzb5efjaNlMjA8"
                    target="_blank"
                    rel="noopener noreferrer nofollow"
                    aria-label="فتح قناة التليجرام"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M21.78 3.46a1.55 1.55 0 0 0-1.63-.22L3.16 9.8c-1.1.43-1.08 1.04-.2 1.31l4.36 1.36 1.68 5.2c.2.56.1.78.7.78.46 0 .66-.21.91-.45l2.1-2.04 4.36 3.22c.8.44 1.38.21 1.58-.74l2.7-12.73c.28-1.13-.43-1.64-1.57-1.25ZM8 12.15l9.86-6.22c.49-.3.94-.14.57.2l-8.14 7.35-.32 3.42L8 12.15Z"/>
                    </svg>

                    <span>تليجرام</span>
                </a>
            </div>

            <div class="arena-header-center">
                <div class="arena-avatar">
                    <img
                        src="assets/img/arena4k-logo.png"
                        alt="أرينا لايف"
                        width="64"
                        height="64"
                    >
                </div>

                <h1 class="arena-site-name">أرينا لايف</h1>
            </div>

            <div class="arena-header-side arena-header-side-left" aria-hidden="true"></div>

        </div>

        <p class="arena-header-subtitle">
            اختر المباراة واضغط على البطاقة للمتابعة
        </p>

    </header>

    <section class="matches-section" aria-label="بطاقات المباريات">

        <?php if (!$matches): ?>

            <div class="empty-state">
                <span class="empty-ball">⚽</span>
                <h2>لا توجد مباريات حالياً</h2>
                <p>ستظهر المباريات هنا عند إضافتها.</p>
            </div>

        <?php else: ?>

            <div class="matches-list">

                <?php foreach ($matches as $match): ?>

                    <a
                        class="match-card"
                        href="go.php?id=<?= (int)$match['id'] ?>"
                        rel="nofollow"
                    >

                        <div class="team-block">
                            <div class="team-logo-wrap">

                                <?php if (!empty($match['team1_logo'])): ?>

                                    <img
                                        class="team-logo"
                                        src="uploads/teams/<?= e(safe_logo_name($match['team1_logo'])) ?>"
                                        alt="<?= e($match['team1_name']) ?>"
                                        loading="lazy"
                                    >

                                <?php else: ?>

                                    <span class="team-logo-fallback">
                                        <?= e(mb_substr($match['team1_name'], 0, 1, 'UTF-8')) ?>
                                    </span>

                                <?php endif; ?>

                            </div>

                            <strong><?= e($match['team1_name']) ?></strong>
                        </div>

                        <div class="match-card-center">
                            <span class="versus-dot">VS</span>
                            <span class="watch-label">مشاهدة</span>
                        </div>

                        <div class="team-block">
                            <div class="team-logo-wrap">

                                <?php if (!empty($match['team2_logo'])): ?>

                                    <img
                                        class="team-logo"
                                        src="uploads/teams/<?= e(safe_logo_name($match['team2_logo'])) ?>"
                                        alt="<?= e($match['team2_name']) ?>"
                                        loading="lazy"
                                    >

                                <?php else: ?>

                                    <span class="team-logo-fallback">
                                        <?= e(mb_substr($match['team2_name'], 0, 1, 'UTF-8')) ?>
                                    </span>

                                <?php endif; ?>

                            </div>

                            <strong><?= e($match['team2_name']) ?></strong>
                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <footer class="public-footer">
        © <?= date('Y') ?> <?= e(SITE_NAME) ?>
    </footer>

</main>

<script src="assets/js/app.js?v=20260828-4"></script>
<script src="assets/js/guard.js?v=20260828-4"></script>

</body>
</html>
