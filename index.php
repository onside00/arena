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
    <meta name="description" content="أرينا لايف - مباريات اليوم">

    <!-- Cache busting حتى لا يبقى المتصفح على CSS قديم -->
    <link rel="stylesheet" href="assets/css/style.css?v=20260828-3">

    <!--
      التنسيق الأساسي للهيدر مدمج هنا عمداً.
      حتى لو كان style.css قديم أو الكاش لم يتحدث، الشعار لن يكبر.
    -->
    <style>
        .arena-home-header,
        .arena-home-header *{
            box-sizing:border-box;
        }

        .arena-home-header{
            width:100%;
            padding:4px 0 14px;
            text-align:center;
        }

        .arena-topbar{
            position:relative;
            min-height:44px;
            display:flex;
            align-items:center;
            justify-content:center;
            width:100%;
            margin:0 0 10px;
        }

        .arena-top-title{
            margin:0;
            padding:0;
            font-family:Tahoma,Arial,sans-serif;
            font-size:16px;
            line-height:1.2;
            font-weight:800;
            color:#f7f9ff;
            white-space:nowrap;
        }

        .arena-telegram{
            position:absolute;
            left:0;
            top:50%;
            transform:translateY(-50%);
            display:inline-flex !important;
            align-items:center !important;
            justify-content:center !important;
            gap:6px !important;
            width:auto !important;
            max-width:none !important;
            min-width:0 !important;
            min-height:34px !important;
            height:34px !important;
            margin:0 !important;
            padding:5px 9px !important;
            border-radius:10px !important;
            color:#fff !important;
            background:#229ED9 !important;
            border:1px solid rgba(255,255,255,.16) !important;
            box-shadow:0 7px 18px rgba(34,158,217,.22) !important;
            text-decoration:none !important;
            font-family:Tahoma,Arial,sans-serif !important;
            font-size:11px !important;
            line-height:1 !important;
            font-weight:800 !important;
            white-space:nowrap !important;
            overflow:hidden !important;
        }

        .arena-telegram-icon{
            display:grid !important;
            place-items:center !important;
            flex:0 0 22px !important;
            width:22px !important;
            height:22px !important;
            min-width:22px !important;
            min-height:22px !important;
            max-width:22px !important;
            max-height:22px !important;
            padding:0 !important;
            margin:0 !important;
            border-radius:7px !important;
            background:rgba(255,255,255,.15) !important;
            overflow:hidden !important;
        }

        .arena-telegram-icon svg{
            display:block !important;
            width:14px !important;
            height:14px !important;
            min-width:14px !important;
            min-height:14px !important;
            max-width:14px !important;
            max-height:14px !important;
            fill:#fff !important;
            margin:0 !important;
            padding:0 !important;
        }

        .arena-logo-wrap{
            display:block !important;
            width:64px !important;
            height:64px !important;
            min-width:64px !important;
            min-height:64px !important;
            max-width:64px !important;
            max-height:64px !important;
            margin:0 auto 6px !important;
            padding:2px !important;
            border-radius:50% !important;
            overflow:hidden !important;
            background:#ffffff !important;
            border:2px solid rgba(255,255,255,.18) !important;
            box-shadow:
                0 8px 22px rgba(0,0,0,.24),
                0 0 0 3px rgba(49,72,167,.11) !important;
        }

        .arena-logo-wrap img,
        img.arena-round-logo{
            display:block !important;
            width:56px !important;
            height:56px !important;
            min-width:56px !important;
            min-height:56px !important;
            max-width:56px !important;
            max-height:56px !important;
            margin:0 !important;
            padding:0 !important;
            border:0 !important;
            border-radius:50% !important;
            object-fit:cover !important;
            object-position:center !important;
        }

        .arena-small-name{
            margin:0 0 3px !important;
            padding:0 !important;
            color:#f7f9ff !important;
            font-family:Tahoma,Arial,sans-serif !important;
            font-size:13px !important;
            line-height:1.25 !important;
            font-weight:800 !important;
        }

        .arena-small-description{
            margin:0 !important;
            padding:0 !important;
            color:#9eabc7 !important;
            font-family:Tahoma,Arial,sans-serif !important;
            font-size:11px !important;
            line-height:1.4 !important;
            font-weight:400 !important;
        }

        @media (max-width:420px){
            .arena-home-header{
                padding-top:2px;
            }

            .arena-topbar{
                min-height:40px;
                margin-bottom:9px;
            }

            .arena-top-title{
                font-size:14px;
            }

            .arena-telegram{
                height:31px !important;
                min-height:31px !important;
                padding:4px 7px !important;
                gap:5px !important;
                font-size:10px !important;
                border-radius:9px !important;
            }

            .arena-telegram-icon{
                flex-basis:20px !important;
                width:20px !important;
                height:20px !important;
                min-width:20px !important;
                min-height:20px !important;
                max-width:20px !important;
                max-height:20px !important;
            }

            .arena-telegram-icon svg{
                width:13px !important;
                height:13px !important;
                min-width:13px !important;
                min-height:13px !important;
                max-width:13px !important;
                max-height:13px !important;
            }

            .arena-logo-wrap{
                width:58px !important;
                height:58px !important;
                min-width:58px !important;
                min-height:58px !important;
                max-width:58px !important;
                max-height:58px !important;
            }

            .arena-logo-wrap img,
            img.arena-round-logo{
                width:50px !important;
                height:50px !important;
                min-width:50px !important;
                min-height:50px !important;
                max-width:50px !important;
                max-height:50px !important;
            }

            .arena-small-name{
                font-size:12px !important;
            }

            .arena-small-description{
                font-size:10px !important;
            }
        }
    </style>
</head>

<body class="public-body">
<main class="public-shell">

    <header class="arena-home-header" aria-labelledby="site-title">

        <div class="arena-topbar">

            <a
                class="arena-telegram"
                href="https://t.me/+9Akzb5efjaNlMjA8"
                target="_blank"
                rel="noopener noreferrer nofollow"
                aria-label="الانضمام إلى قناة أرينا على تليجرام"
            >
                <span class="arena-telegram-icon" aria-hidden="true">
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                        focusable="false"
                        style="display:block;width:14px;height:14px;fill:#ffffff"
                    >
                        <path d="M21.78 3.46a1.55 1.55 0 0 0-1.63-.22L3.16 9.8c-1.1.43-1.08 1.04-.2 1.31l4.36 1.36 1.68 5.2c.2.56.1.78.7.78.46 0 .66-.21.91-.45l2.1-2.04 4.36 3.22c.8.44 1.38.21 1.58-.74l2.7-12.73c.28-1.13-.43-1.64-1.57-1.25ZM8 12.15l9.86-6.22c.49-.3.94-.14.57.2l-8.14 7.35-.32 3.42L8 12.15Z"/>
                    </svg>
                </span>

                <span>قناة التليجرام</span>
            </a>

            <div class="arena-top-title">أرينا لايف</div>

        </div>

        <div
            class="arena-logo-wrap"
            style="
                width:64px;
                height:64px;
                max-width:64px;
                max-height:64px;
                border-radius:50%;
                overflow:hidden;
                margin:0 auto 6px;
            "
        >
            <img
                class="arena-round-logo"
                src="assets/img/arena4k-logo.png"
                alt="أرينا 4K"
                width="56"
                height="56"
                style="
                    display:block;
                    width:56px;
                    height:56px;
                    max-width:56px;
                    max-height:56px;
                    border-radius:50%;
                    object-fit:cover;
                    margin:0;
                "
            >
        </div>

        <h1 id="site-title" class="arena-small-name"><?= e(SITE_NAME) ?></h1>
        <p class="arena-small-description">اختر المباراة واضغط على البطاقة للمتابعة</p>

    </header>

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

                        <span class="versus-dot" aria-hidden="true">VS</span>

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

<script src="assets/js/app.js?v=20260828-3"></script>
<script src="assets/js/guard.js?v=20260828-3"></script>

</body>
</html>
