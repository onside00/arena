<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كورفا سبورتس | Curva Sports - بث مباشر وأخبار الرياضة</title>
    
    <!-- CSS Link -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Google Fonts (Cairo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header / Navigation Bar -->
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <!-- استبدل logo.png باسم ملف اللوجو الخاص بك داخل مجلد assets/img/ -->
                <img src="assets/img/logo.png" alt="كورفا سبورتس - Curva Sports" class="site-logo">
                <span class="brand-name">كورفا سبورتس</span>
            </a>

            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="active">الرئيسية</a></li>
                    <li><a href="#matches">مباريات اليوم</a></li>
                    <li><a href="#news">الأخبار</a></li>
                    <li><a href="#contact">اتصل بنا</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container">
            
            <!-- Live Matches Header -->
            <section class="section-title">
                <h2>مباريات اليوم - بث مباشر</h2>
                <span class="live-indicator"><span class="pulse"></span> مباشر الان</span>
            </section>

            <!-- Matches Cards Grid Container -->
            <div class="matches-grid" id="matches">
                
                <!-- Match Card Sample -->
                <div class="match-card">
                    <div class="team team-home">
                        <img src="assets/img/team1.png" alt="الفريق الأول" class="team-flag">
                        <span class="team-name">الفريق المستضيف</span>
                    </div>

                    <div class="match-info">
                        <span class="match-time">10:00 مساءً</span>
                        <span class="match-status">لم تبدأ</span>
                        <a href="#" class="btn-watch">شاهد البث</a>
                    </div>

                    <div class="team team-away">
                        <img src="assets/img/team2.png" alt="الفريق الثاني" class="team-flag">
                        <span class="team-name">الفريق الضيف</span>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer Area -->
    <footer class="main-footer">
        <div class="container">
            <p>جميع الحقوق محفوظة &copy; 2026 <strong>كورفا سبورتس | Curva Sports</strong></p>
        </div>
    </footer>

</body>
</html>