<?php
declare(strict_types=1);
require __DIR__ . '/security.php';

$adminEntry = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
if (!defined('ARENA_ADMIN_ENTRY') || !preg_match('/^panel-[a-f0-9]{32}\.php$/', $adminEntry)) {
    http_response_code(404);
    exit;
}

// The hidden admin entry is intentionally exempt from public country/device restrictions.
// Access is still protected by the randomized URL, password, CSRF, session controls, and login throttling.
require __DIR__ . '/config.php';

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store, max-age=0');

$action = $_GET['action'] ?? 'dashboard';
$message = '';
$error = '';

function arena_resequence_matches(mysqli $db, array $ids): void {
    $stmt = $db->prepare('UPDATE matches SET display_order = ? WHERE id = ?');
    $position = 10;
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id < 1) {
            continue;
        }
        $stmt->bind_param('ii', $position, $id);
        $stmt->execute();
        $position += 10;
    }
}

function arena_current_order(mysqli $db): array {
    $result = $db->query('SELECT id FROM matches ORDER BY display_order ASC, match_time ASC, id ASC');
    return array_map(static fn(array $row): int => (int)$row['id'], $result->fetch_all(MYSQLI_ASSOC));
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . $adminEntry);
    exit;
}

if (is_admin()) {
    $last = (int)($_SESSION['admin_last_activity'] ?? 0);
    if ($last > 0 && (time() - $last) > 7200) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ' . $adminEntry);
        exit;
    }
    $_SESSION['admin_last_activity'] = time();
}

if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $limit = arena_login_limit_status();
        if ($limit['blocked']) {
            $minutes = max(1, (int)ceil($limit['remaining'] / 60));
            $error = 'تم إيقاف محاولات الدخول مؤقتاً. حاول بعد ' . $minutes . ' دقيقة.';
        } else {
            $password = (string)($_POST['password'] ?? '');

            if (ADMIN_PASSWORD_HASH === '') {
                http_response_code(503);
                exit('Admin password is not configured.');
            }

            if (password_verify($password, ADMIN_PASSWORD_HASH)) {
                arena_login_limit_success();
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_last_activity'] = time();
                header('Location: ' . $adminEntry);
                exit;
            }

            arena_login_limit_fail();
            $error = 'كلمة المرور غير صحيحة.';
        }
    }
    ?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>دخول الإدارة</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-body">
<main class="login-shell">
    <form method="post" class="admin-card login-card" autocomplete="off">
        <div class="brand brand-center"><span class="brand-mark">A</span><span>لوحة الإدارة</span></div>
        <h1>تسجيل الدخول</h1>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>كلمة المرور</label>
        <input type="password" name="password" required autofocus>
        <button type="submit" class="primary-btn" style="width:100%;margin-top:16px">دخول</button>
    </form>
</main>
</body>
</html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        $formAction = (string)($_POST['form_action'] ?? '');

        if ($formAction === 'save') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
            $team1 = trim((string)($_POST['team1_name'] ?? ''));
            $team2 = trim((string)($_POST['team2_name'] ?? ''));
            $league = trim((string)($_POST['league'] ?? ''));
            $matchTimeRaw = trim((string)($_POST['match_time'] ?? ''));
            $redirectUrl = trim((string)($_POST['redirect_url'] ?? ''));
            $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

            if ($team1 === '' || $team2 === '' || $league === '' || $matchTimeRaw === '' || $redirectUrl === '') {
                throw new RuntimeException('جميع الحقول المطلوبة يجب تعبئتها.');
            }
            if (mb_strlen($team1) > 100 || mb_strlen($team2) > 100 || mb_strlen($league) > 100) {
                throw new RuntimeException('أحد الحقول النصية أطول من المسموح.');
            }
            if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('رابط التحويل غير صالح.');
            }
            $scheme = strtolower((string)parse_url($redirectUrl, PHP_URL_SCHEME));
            if (!in_array($scheme, ['http', 'https'], true)) {
                throw new RuntimeException('يسمح فقط بروابط HTTP/HTTPS.');
            }

            $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $matchTimeRaw);
            if (!$dt) {
                throw new RuntimeException('وقت المباراة غير صالح.');
            }
            $matchTime = $dt->format('Y-m-d H:i:s');

            $old1 = null;
            $old2 = null;
            if ($id > 0) {
                $stmt = $db->prepare('SELECT team1_logo, team2_logo FROM matches WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $existing = $stmt->get_result()->fetch_assoc();
                if (!$existing) {
                    throw new RuntimeException('المباراة غير موجودة.');
                }
                $old1 = $existing['team1_logo'];
                $old2 = $existing['team2_logo'];
            }

            $logo1 = upload_team_logo('team1_logo', $old1);
            $logo2 = upload_team_logo('team2_logo', $old2);

            if ($id > 0) {
                $stmt = $db->prepare("
                    UPDATE matches
                    SET team1_name=?, team1_logo=?, team2_name=?, team2_logo=?,
                        league=?, match_time=?, redirect_url=?, status=?
                    WHERE id=?
                ");
                $stmt->bind_param('ssssssssi', $team1, $logo1, $team2, $logo2, $league, $matchTime, $redirectUrl, $status, $id);
                $stmt->execute();
                $message = 'تم تحديث المباراة بنجاح.';
            } else {
                $maxOrder = (int)$db->query('SELECT COALESCE(MAX(display_order),0) AS m FROM matches')->fetch_assoc()['m'];
                $displayOrder = $maxOrder + 10;
                $stmt = $db->prepare("
                    INSERT INTO matches
                    (team1_name, team1_logo, team2_name, team2_logo, league, match_time, redirect_url, status, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('ssssssssi', $team1, $logo1, $team2, $logo2, $league, $matchTime, $redirectUrl, $status, $displayOrder);
                $stmt->execute();
                $message = 'تمت إضافة المباراة بنجاح.';
            }
            $action = 'dashboard';
        }

        if ($formAction === 'delete') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('معرف المباراة غير صالح.');
            }
            $stmt = $db->prepare('SELECT team1_logo, team2_logo FROM matches WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing) {
                foreach (['team1_logo', 'team2_logo'] as $key) {
                    if (!empty($existing[$key])) {
                        $path = __DIR__ . '/uploads/teams/' . basename($existing[$key]);
                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }
                }
                $del = $db->prepare('DELETE FROM matches WHERE id = ?');
                $del->bind_param('i', $id);
                $del->execute();
                arena_resequence_matches($db, arena_current_order($db));
            }
            $message = 'تم حذف المباراة.';
            $action = 'dashboard';
        }

        if ($formAction === 'toggle') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('معرف المباراة غير صالح.');
            }
            $stmt = $db->prepare("UPDATE matches SET status = IF(status='active','inactive','active') WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $message = 'تم تغيير حالة المباراة.';
            $action = 'dashboard';
        }

        if ($formAction === 'move') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $direction = (string)($_POST['direction'] ?? '');
            if (!$id || !in_array($direction, ['up', 'down'], true)) {
                throw new RuntimeException('طلب الترتيب غير صالح.');
            }

            $ids = arena_current_order($db);
            $index = array_search((int)$id, $ids, true);
            if ($index !== false) {
                $target = $direction === 'up' ? $index - 1 : $index + 1;
                if (isset($ids[$target])) {
                    [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
                    $db->begin_transaction();
                    try {
                        arena_resequence_matches($db, $ids);
                        $db->commit();
                    } catch (Throwable $e) {
                        $db->rollback();
                        throw $e;
                    }
                    $message = 'تم تغيير ترتيب البطاقة.';
                }
            }
            $action = 'dashboard';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$editMatch = null;
if ($action === 'edit') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $db->prepare('SELECT * FROM matches WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $editMatch = $stmt->get_result()->fetch_assoc();
    }
}

$stats = $db->query("
    SELECT COUNT(*) AS total_matches,
           SUM(status='active') AS active_matches
    FROM matches
")->fetch_assoc();

$list = $db->query('SELECT * FROM matches ORDER BY display_order ASC, match_time ASC, id ASC');
$matches = $list->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>لوحة الإدارة</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-body">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e($adminEntry) ?>"><span class="brand-mark">A</span><span>لوحة الإدارة</span></a>
        <nav class="nav">
            <a href="index.php" target="_blank" rel="noopener">عرض الموقع</a>
            <a href="<?= e($adminEntry) ?>?action=logout">خروج</a>
        </nav>
    </div>
</header>

<main class="container admin-main">
    <?php if ($message): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($action === 'add' || ($action === 'edit' && $editMatch)): ?>
        <?php
        $m = $editMatch ?? [
            'id' => '', 'team1_name' => '', 'team2_name' => '', 'league' => '',
            'match_time' => date('Y-m-d H:i:s'), 'redirect_url' => '', 'status' => 'active',
            'team1_logo' => '', 'team2_logo' => '',
        ];
        $inputTime = (new DateTimeImmutable($m['match_time']))->format('Y-m-d\TH:i');
        ?>
        <div class="admin-heading">
            <div>
                <h1><?= $editMatch ? 'تعديل المباراة' : 'إضافة مباراة' ?></h1>
                <p>أدخل تفاصيل المباراة والرابط المخفي.</p>
            </div>
            <a class="secondary-btn" href="<?= e($adminEntry) ?>">رجوع</a>
        </div>

        <form method="post" enctype="multipart/form-data" class="admin-card form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="save">
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">

            <div>
                <label>اسم الفريق الأول *</label>
                <input type="text" name="team1_name" maxlength="100" required value="<?= e($m['team1_name']) ?>">
            </div>
            <div>
                <label>شعار الفريق الأول</label>
                <input type="file" name="team1_logo" accept="image/png,image/jpeg,image/webp,image/gif,image/avif">
                <small class="upload-note">PNG / JPG / JPEG / WebP / GIF / AVIF — حتى 5MB</small>
                <?php if (!empty($m['team1_logo'])): ?><img class="logo-preview" src="uploads/teams/<?= e(safe_logo_name($m['team1_logo'])) ?>" alt=""><?php endif; ?>
            </div>

            <div>
                <label>اسم الفريق الثاني *</label>
                <input type="text" name="team2_name" maxlength="100" required value="<?= e($m['team2_name']) ?>">
            </div>
            <div>
                <label>شعار الفريق الثاني</label>
                <input type="file" name="team2_logo" accept="image/png,image/jpeg,image/webp,image/gif,image/avif">
                <small class="upload-note">PNG / JPG / JPEG / WebP / GIF / AVIF — حتى 5MB</small>
                <?php if (!empty($m['team2_logo'])): ?><img class="logo-preview" src="uploads/teams/<?= e(safe_logo_name($m['team2_logo'])) ?>" alt=""><?php endif; ?>
            </div>

            <div>
                <label>البطولة *</label>
                <input type="text" name="league" maxlength="100" required value="<?= e($m['league']) ?>">
            </div>
            <div>
                <label>وقت المباراة *</label>
                <input type="datetime-local" name="match_time" required value="<?= e($inputTime) ?>">
            </div>
            <div class="full-span">
                <label>الرابط المخفي *</label>
                <input type="url" name="redirect_url" maxlength="500" required value="<?= e($m['redirect_url']) ?>" placeholder="https://t.me/...">
            </div>
            <div>
                <label>الحالة</label>
                <select name="status">
                    <option value="active" <?= $m['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                    <option value="inactive" <?= $m['status'] === 'inactive' ? 'selected' : '' ?>>متوقف</option>
                </select>
            </div>
            <div class="full-span">
                <button class="primary-btn" type="submit"><?= $editMatch ? 'حفظ التعديلات' : 'إضافة المباراة' ?></button>
            </div>
        </form>
    <?php else: ?>
        <div class="admin-heading">
            <div>
                <h1>لوحة التحكم</h1>
                <p>إدارة المباريات وترتيب ظهور البطاقات.</p>
            </div>
            <a class="primary-btn" href="<?= e($adminEntry) ?>?action=add">+ إضافة مباراة</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span>إجمالي المباريات</span><strong><?= number_format((int)$stats['total_matches']) ?></strong></div>
            <div class="stat-card"><span>المباريات الفعالة</span><strong><?= number_format((int)$stats['active_matches']) ?></strong></div>
        </div>

        <div class="admin-card table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>المباراة</th>
                        <th>البطولة</th>
                        <th>الوقت</th>
                        <th>الحالة</th>
                        <th>الرابط العام</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$matches): ?>
                    <tr><td colspan="7">لا توجد مباريات.</td></tr>
                <?php else: ?>
                    <?php foreach ($matches as $index => $match): ?>
                        <tr>
                            <td class="order-cell">
                                <span class="order-number"><?= $index + 1 ?></span>
                            </td>
                            <td><?= e($match['team1_name']) ?> × <?= e($match['team2_name']) ?></td>
                            <td><?= e($match['league']) ?></td>
                            <td><?= e($match['match_time']) ?></td>
                            <td><?= $match['status'] === 'active' ? 'فعال' : 'متوقف' ?></td>
                            <td><code>go.php?id=<?= (int)$match['id'] ?></code></td>
                            <td>
                                <div class="actions">
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="form_action" value="move">
                                        <input type="hidden" name="id" value="<?= (int)$match['id'] ?>">
                                        <input type="hidden" name="direction" value="up">
                                        <button class="small-btn order-btn" type="submit" <?= $index === 0 ? 'disabled' : '' ?>>↑ فوق</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="form_action" value="move">
                                        <input type="hidden" name="id" value="<?= (int)$match['id'] ?>">
                                        <input type="hidden" name="direction" value="down">
                                        <button class="small-btn order-btn" type="submit" <?= $index === count($matches) - 1 ? 'disabled' : '' ?>>↓ تحت</button>
                                    </form>
                                    <a class="small-btn" href="<?= e($adminEntry) ?>?action=edit&amp;id=<?= (int)$match['id'] ?>">تعديل</a>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="form_action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int)$match['id'] ?>">
                                        <button class="small-btn" type="submit">تفعيل/إيقاف</button>
                                    </form>
                                    <form method="post" class="confirm-delete">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$match['id'] ?>">
                                        <button class="small-btn danger" type="submit">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<script src="assets/js/admin.js"></script>
</body>
</html>
