<?php
declare(strict_types=1);

// الحفاظ على كامل ملفات ووظائف الحماية والأمان الأصلية
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
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b1329">

    <title>النجم لايف - مباريات اليوم</title>
    <meta name="description" content="النجم لايف - جدول ومباريات اليوم بث مباشر">

    <!-- CSS الأصلي -->
    <link rel="stylesheet" href="assets/css/style.css?v=20260829-1">

    <!-- تحسينات التصميم والألوان الجملية الاحترافية -->
    <style>
        :root {
            --najm-bg: #0b1329;
            --najm-card-bg: rgba(21, 32, 63, 0.75);
            --najm-accent: #f59e0b;
            --najm-accent-glow: rgba(245, 158, 11, 0.3);
            --najm-blue: #3b82f6;
            --najm-text: #f8fafc;
            --najm-text-muted: #94a3b8;
        }

        body.public-body {
            background-color: var(--najm-bg);
            background-image: radial-gradient(circle at 50% 0%, #1e293b 0%, #0b1329 70%);
            color: var(--najm-text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        .public-shell {
            max-width: 680px;
            margin: 0 auto;
            padding: 16px;
        }

        /* ترويسة الموقع */
        .arena-main-header {
            text-align: center;
            padding: 24px 16px 16px;
            margin-bottom: 24px;
        }

        .arena-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .arena-header-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin: 0 auto;
        }

        .arena-avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(135deg, var(--najm-accent), var(--najm-blue));
            box-shadow: 0 0 20px var(--najm-accent-glow);
        }

        .arena-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #000;
        }

        .arena-site-name {
            font-size: 1.65rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(90deg, #ffffff, var(--najm-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .arena-telegram-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(40, 168, 233, 0.15);
            color: #28a8ea;
            border: 1px solid rgba(40, 168, 233, 0.3);
            padding: 8px 14px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .arena-telegram-btn:hover {
            background: #28a8ea;
            color: #fff;
            box-shadow: 0 0 12px rgba(40, 168, 233, 0.4);
        }

        .arena-telegram-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* شارة البث المباشر الاحترافية */
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-top: 8px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulse 1.6s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* كروت المباريات */
        .matches-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .match-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--najm-card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 14px 18px;
            text-decoration: none;
            color: var(--najm-text);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .match-card:hover {
            transform: translateY(-2px);
            border-color: rgba(245, 158, 11, 0.4);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .team-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
            text-align: center;
        }

        .team-logo-wrap {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .team-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));
        }

        .team-logo-fallback {
            width: 44px;
            height: 44px;
            background: #1e293b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--najm-accent);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .team-block strong {
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .match-card-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 0 12px;
        }

        .versus-dot {
            font-size: 0.75rem;
            font-weight: 900;
            color: var(--najm-accent);
            background: rgba(245, 158, 11, 0.1);
            padding: 2px 8px;
            border-radius: 8px;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .watch-label {
            font-size: 0.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--najm-accent), #d97706);
            color: #000;
            padding: 6px 16px;
            border-radius: 20px;
            box-shadow: 0 2px 8px var(--najm-accent-glow);
        }

        /* الحالة الفارغة */
        .empty-state {
            text-align: center;
            padding: 48px 16px;
            background: var(--najm-card-bg);
            border-radius: 16px;
            border: 1px dashed rgba(255, 255, 255, 0.15);
        }

        .empty-ball {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 12px;
            opacity: 0.8;
        }

        .public-footer {
            text-align: center;
            padding: 28px 0 16px;
            color: var(--najm-text-muted);
            font-size: 0.82rem;
        }
    </style>
</head>

<body class="public-body">

<main class="public-shell">

    <header class="arena-main-header">

        <div class="arena-header-row">

            <div class="arena-header-side arena-header-side-right">
                <a
                    class="arena-telegram-btn"
                    href="https://t.me/onside_plus"
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
                    <!-- الصورة الجديدة لشعار النجم لايف -->
                    <img
                        src="assets/img/najm-live-logo.png"
                        alt="النجم لايف"
                        width="64"
                        height="64"
                    >
                </div>

                <h1 class="arena-site-name">النجم لايف</h1>

                <div class="live-badge">
                    <span class="pulse-dot"></span>
                    <span>تغطية مباشرة</span>
                </div>
            </div>

            <div class="arena-header-side arena-header-side-left" aria-hidden="true"></div>

        </div>

    </header>

    <section class="matches-section" aria-label="بطاقات المباريات">

        <?php if (!$matches): ?>

            <div class="empty-state">
                <span class="empty-ball">⚽</span>
                <h2>لا توجد مباريات حالياً</h2>
                <p>ستظهر المباريات هنا فور إضافتها لقائمة اليوم.</p>
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
        © <?= date('Y') ?> النجم لايف - جميع الحقوق محفوظة
    </footer>

</main>

<!-- السكربتات الأصلية كما هي للحفاظ على الأمان والتفاعل -->
<script src="assets/js/app.js?v=20260829-1"></script>
<script src="assets/js/guard.js?v=20260829-1"></script>

</body>
</html>