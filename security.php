<?php
declare(strict_types=1);

/**
 * Public policy:
 * - Arab countries only.
 * - Phone browsers only.
 * - Desktop/laptop and foreign visitors see maintenance.
 * - No public IP-ban is created.
 */
const ARENA_ALLOWED_COUNTRIES = [
    'DZ','BH','KM','DJ','EG','IQ','JO','KW','LB','LY','MR',
    'MA','OM','PS','QA','SA','SO','SD','SY','TN','AE','YE'
];

function arena_country(): string {
    return strtoupper(trim((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
}

function arena_country_allowed(): bool {
    return in_array(arena_country(), ARENA_ALLOWED_COUNTRIES, true);
}

function arena_is_mobile_phone(): bool {
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $mobileHint = trim((string)($_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? ''));
    $platform = strtolower(trim((string)($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? ''), "\"' "));

    // Reject clear desktop platform signals first.
    if (in_array($platform, ['windows', 'macos', 'chrome os', 'linux'], true)) {
        return false;
    }

    if ($ua === '') {
        return false;
    }

    if ((bool)preg_match('/iPhone|iPod|Android.*Mobile|Windows Phone|IEMobile|Opera Mini|Opera Mobi|BlackBerry|BB10|Mobile.*Firefox/i', $ua)) {
        return true;
    }

    return $mobileHint === '?1';
}

function arena_show_maintenance(): never {
    http_response_code(503);
    header('Cache-Control: no-store, max-age=0');
    header('Pragma: no-cache');
    header('Retry-After: 3600');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    require __DIR__ . '/maintenance.php';
    exit;
}

function arena_enforce_public_access(): void {
    if (!arena_country_allowed() || !arena_is_mobile_phone()) {
        arena_show_maintenance();
    }
}

function arena_enforce_admin_country(): void {
    if (!arena_country_allowed()) {
        arena_show_maintenance();
    }
}

function arena_client_ip(): string {
    $cf = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) {
        return $cf;
    }
    return trim((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * Admin-login throttling only.
 * 5 failed passwords in 15 minutes => 30-minute login lock.
 */
function arena_login_limit_file(): string {
    $key = hash('sha256', arena_client_ip());
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'arena_login_' . $key . '.json';
}

function arena_login_limit_status(): array {
    $file = arena_login_limit_file();
    if (!is_file($file)) {
        return ['blocked' => false, 'remaining' => 0];
    }

    $raw = @file_get_contents($file);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        return ['blocked' => false, 'remaining' => 0];
    }

    $now = time();
    $lockedUntil = (int)($data['locked_until'] ?? 0);
    if ($lockedUntil > $now) {
        return ['blocked' => true, 'remaining' => $lockedUntil - $now];
    }

    return ['blocked' => false, 'remaining' => 0];
}

function arena_login_limit_fail(): void {
    $file = arena_login_limit_file();
    $now = time();
    $window = 15 * 60;
    $lock = 30 * 60;

    $fp = @fopen($file, 'c+');
    if (!$fp) {
        usleep(700000);
        return;
    }

    try {
        flock($fp, LOCK_EX);
        rewind($fp);
        $raw = stream_get_contents($fp);
        $data = $raw ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        $first = (int)($data['first'] ?? $now);
        $count = (int)($data['count'] ?? 0);

        if (($now - $first) > $window) {
            $first = $now;
            $count = 0;
        }

        $count++;
        $lockedUntil = $count >= 5 ? $now + $lock : 0;

        $new = json_encode([
            'first' => $first,
            'count' => $count,
            'locked_until' => $lockedUntil,
        ], JSON_UNESCAPED_SLASHES);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $new ?: '{}');
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }

    usleep(900000);
}

function arena_login_limit_success(): void {
    @unlink(arena_login_limit_file());
}
