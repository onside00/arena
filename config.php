<?php
declare(strict_types=1);

define('DB_HOST', getenv('SPORTS_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('SPORTS_DB_NAME') ?: 'sports_live');
define('DB_USER', getenv('SPORTS_DB_USER') ?: 'sports_user');
define('DB_PASS', getenv('SPORTS_DB_PASS') ?: 'CHANGE_ME_DB_PASSWORD');

define('SITE_NAME', 'أرينا لايف');
define('SITE_TIMEZONE', 'Asia/Baghdad');
define('ADMIN_PASSWORD_HASH', getenv('SPORTS_ADMIN_PASSWORD_HASH') ?: '');

date_default_timezone_set(SITE_TIMEZONE);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Database connection failed.');
}

/**
 * v2 schema upgrade. Existing installations are upgraded automatically once.
 */
function arena_ensure_schema(mysqli $db): void {
    $check = $db->query("SHOW COLUMNS FROM matches LIKE 'display_order'");
    if ($check->num_rows === 0) {
        try {
            $db->query("ALTER TABLE matches ADD COLUMN display_order INT NOT NULL DEFAULT 0 AFTER status");
        } catch (mysqli_sql_exception $e) {
            // 1060 = duplicate column; another concurrent request may have created it.
            if ((int)$e->getCode() !== 1060) {
                throw $e;
            }
        }
        $db->query("UPDATE matches SET display_order = id * 10 WHERE display_order = 0");
    }
}

arena_ensure_schema($db);

ini_set('session.use_strict_mode', '1');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ARENA_SESSID');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function is_admin(): bool {
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . basename((string)($_SERVER['SCRIPT_NAME'] ?? '')));
        exit;
    }
}

function safe_logo_name(?string $filename): string {
    return basename((string)$filename);
}

/**
 * Accept safe raster web image formats. SVG is intentionally excluded because it can carry active content.
 * Supported: PNG, JPG/JPEG, WebP, GIF, AVIF.
 */
function upload_team_logo(string $fieldName, ?string $oldFile = null): ?string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('فشل رفع الصورة.');
    }

    if ((int)$file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('حجم الصورة يجب ألا يتجاوز 5MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        throw new RuntimeException('الملف المرفوع ليس صورة صالحة.');
    }

    $detectedMime = strtolower((string)($imageInfo['mime'] ?? $mime));
    $allowed = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    if (!isset($allowed[$detectedMime])) {
        throw new RuntimeException('الصيغة غير مدعومة. استخدم PNG أو JPG أو WebP أو GIF أو AVIF.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$detectedMime];
    $targetDir = __DIR__ . '/uploads/teams';

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        throw new RuntimeException('تعذر إنشاء مجلد الصور.');
    }

    $target = $targetDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('تعذر حفظ الصورة.');
    }
    @chmod($target, 0644);

    if ($oldFile) {
        $oldPath = $targetDir . '/' . basename($oldFile);
        if (is_file($oldPath) && $oldPath !== $target) {
            @unlink($oldPath);
        }
    }

    return $name;
}
