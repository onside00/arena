<?php
declare(strict_types=1);

require __DIR__ . '/security.php';
arena_enforce_public_access();
require __DIR__ . '/config.php';

$sql = "
    SELECT
        id,
        team1_name,
        team1_logo,
        team2_name,
        team2_logo,
        league,
        match_time
    FROM matches
    WHERE status = 'active'
      AND match_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY display_order ASC, match_time ASC, id ASC
";

$result = $db->query($sql);
$matches = $result->fetch_all(MYSQLI_ASSOC);

function arena_match_state(string $matchTime): array {
    $start = new DateTimeImmutable($matchTime);
    $now = new DateTimeImmutable('now');

    $seconds = $now->getTimestamp() - $start->getTimestamp();

    if ($seconds >= -900 && $seconds <= 3 * 3600) {
        return ['live', 'مباشر'];
    }

    if ($seconds > 3 * 3600) {
        return ['ended', 'انتهت'];
    }

    return ['upcoming', 'قريباً'];
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">

    <title><?= e(SITE_NAME) ?></title>
    <meta name="description" content="أرينا لايف - مباريات اليوم والبث المباشر">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="public-body">

<header class="public-header">
    <div class="public-header-inner">

        <a
            class="telegram-home"
            href="https://t.me/onside_plus"
            target="_blank"
            rel="noopener noreferrer nofollow"
            aria-label="الرئيسية على تليجرام"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M21.9 4.1c.3-1.3-.5-1.8-1.6-1.4L2.8 9.5c-1.2.5-1.2 1.2-.2 1.5l4.5 1.4 1.7 5.3c.2.6.1.8.7.8.5 0 .7-.2 1-.5l2.2-2.2 4.6 3.4c.9.5 1.5.2 1.7-.8l2.9-14.3ZM8 12.1l10.4-6.5c.5-.3 1-.1.6.2l-8.6 7.8-.3 3.6L8 12.1Z"/>
            </svg>
            <span>الرئيسية</span>
        </a>

        <div class="public-brand" aria-label="<?= e(SITE_NAME) ?>">
            <div class="public-logo-ring">
                <img src="assets/img/logo.png" alt="<?= e(SITE_NAME) ?>">
            </div>
            <h1><?= e(SITE_NAME) ?></h1>
        </div>

        <div class="public-header-spacer" aria-hidden="true"></div>

    </div>
</header>

<main class="public-container">

    <section class="matches-section" aria-label="مباريات اليوم">

        <div class="matches-heading">
            <div>
                <span class="matches-eyebrow">LIVE MATCHES</span>
                <h2>مباريات اليوم</h2>
            </div>

            <span class="matches-count">
                <?= count($matches) ?> مباراة
            </span>
        </div>

        <?php if (!$matches): ?>

            <div class="empty-state">
                <span class="empty-ball">⚽</span>
                <h2>لا توجد مباريات حالياً</h2>
                <p>ستظهر المباريات هنا عند إضافتها من لوحة التحكم.</p>
            </div>

        <?php else: ?>

            <div class="matches-grid">

                <?php foreach ($matches as $match): ?>
                    <?php
                        [$stateClass, $stateText] = arena_match_state($match['match_time']);
                        $displayTime = (new DateTimeImmutable($match['match_time']))->format('H:i');
                    ?>

                    <article class="match-card">

                        <div class="match-card-top">
                            <span class="match-league">
                                <?= e($match['league']) ?>
                            </span>

                            <span class="match-status <?= e($stateClass) ?>">
                                <?php if ($stateClass === 'live'): ?>
                                    <span class="live-dot" aria-hidden="true"></span>
                                <?php endif; ?>

                                <?= e($stateText) ?>
                            </span>
                        </div>

                        <div class="match-time">
                            <?= e($displayTime) ?>
                        </div>

                        <div class="match-teams">

                            <div class="match-team">
                                <div class="match-team-logo">
                                    <?php if (!empty($match['team1_logo'])): ?>
                                        <img
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

                                <strong>
                                    <?= e($match['team1_name']) ?>
                                </strong>
                            </div>

                            <span class="match-vs" aria-hidden="true">VS</span>

                            <div class="match-team">
                                <div class="match-team-logo">
                                    <?php if (!empty($match['team2_logo'])): ?>
                                        <img
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

                                <strong>
                                    <?= e($match['team2_name']) ?>
                                </strong>
                            </div>

                        </div>

                        <a
                            class="watch-live-btn"
                            href="go.php?id=<?= (int)$match['id'] ?>"
                            rel="nofollow"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 5v14l11-7z"/>
                            </svg>

                            <span>مشاهدة المباراة</span>
                        </a>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <footer class="public-footer">
        © <?= date('Y') ?> <?= e(SITE_NAME) ?>
    </footer>

</main>

<script src="assets/js/app.js"></script>
</body>
</html>
