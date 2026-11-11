<?php
// Session Management & Output Buffering (prevents header warnings)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Istanbul');

// General Site Configuration
define('SITE_NAME', 'Auronova Akademi');

// Auto-detect environment (Local vs Live Server)
$isLocal = false;
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '::1') !== false) {
        $isLocal = true;
    }
} elseif (php_sapi_name() === 'cli') {
    $isLocal = true;
}

if ($isLocal) {
    // Yerel (XAMPP / Localhost) Ayarları
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $subfolder = (strpos($uri, '/akademi') !== false || (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/akademi') !== false)) ? '/akademi' : '';
    define('SITE_URL', $protocol . $host . $subfolder);

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sezgin_akademi_db'); // Yerel veritabanı adınız
    define('DB_USER', 'root');              // XAMPP varsayılan kullanıcısı
    define('DB_PASS', '');                  // XAMPP varsayılan parolası
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Canlı Sunucu (Plesk / Production) Ayarları
    define('SITE_URL', 'https://akademi.auronovasoftware.com.tr');

    define('DB_HOST', 'localhost:3306');
    define('DB_NAME', 'sezgin_uzuncelebi_auronova_akademi_db'); // Plesk veritabanı adı
    define('DB_USER', 'sezgin_uzuncelebi_akademi_user'); // Plesk veritabanı kullanıcısı
    define('DB_PASS', '@tlas2024s'); // Plesk veritabanı parolası
    define('DB_CHARSET', 'utf8mb4');
}

// Bakım Modu (true yapıldığında site ziyaretçilere kapatılır, sadece Adminler giriş yapıp erişebilir)
define('MAINTENANCE_MODE', false);

// Bakım Modu Kontrolü
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE) {
    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
    $isLoginOrAuth = strpos($currentScript, 'login') !== false || strpos($currentScript, 'process_login') !== false || strpos($currentScript, 'logout') !== false;

    if (!$isAdmin && !$isLoginOrAuth) {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Bakım Modundayız — Auronova Akademi</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center vh-100">
            <div class="text-center p-4 bg-white rounded-4 shadow-sm" style="max-width: 500px;">
                <div class="mb-3 text-primary">
                    <i class="fa-solid fa-screwdriver-wrench display-3"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">Bakım Modundayız</h2>
                <p class="text-muted mb-4">Auronova Akademi platformumuzda şu anda planlı bir güncelleme / bakım yapılmaktadır. En kısa sürede yeniden hizmetinizde olacağız.</p>
                <a href="<?php echo defined('SITE_URL') ? SITE_URL . '/auth/login.php' : '#'; ?>" class="btn btn-primary btn-sm">Yönetici Girişi</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
