<?php
declare(strict_types=1);
if (!headers_sent()) {
    http_response_code(503);
    header('Cache-Control: no-store, max-age=0');
    header('Retry-After: 3600');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>كورفا سبورتس — صيانة</title>
    <link rel="stylesheet" href="/assets/css/maintenance.css">
</head>
<body>
<main class="maintenance-shell">
    <section class="maintenance-card">
        <!-- استبدل logo.png باسم ملف اللوجو الخاص بك إذا كان مختلفاً -->
        <img class="maintenance-logo" src="/assets/img/logo.png" alt="Curva Sports">
        <p class="maintenance-kicker">CURVA SPORTS</p>
        <h1>الموقع في وضع صيانة</h1>
        <p class="maintenance-text">هذه الصفحة غير متاحة حالياً من جهازك أو موقعك. يمكنك المتابعة من موقعنا الآخر.</p>
        <a class="maintenance-button" href="https://www.curvasports.com" target="_blank" rel="noopener noreferrer">شاهد من الموقع</a>
    </section>
</main>
</body>
</html>