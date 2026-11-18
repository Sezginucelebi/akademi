<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';

if (isLoggedIn()) {
    $user = currentUser();
    $destination = $user['role'] === 'teacher' ? 'teacher' : ($user['role'] === 'admin' ? 'admin' : 'student');
    header('Location: ' . SITE_URL . '/' . $destination . '/dashboard.php');
    exit;
}

$pageTitle = 'Giriş Yap — Auronova Akademi';
$loginLogoSize = (($_GET['open_register'] ?? '') === '1') ? 128 : 64;
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container py-5 main-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 p-md-5 text-center">
                <img src="<?php echo SITE_URL; ?>/assets/img/icon.png" alt="Auronova Akademi" class="mx-auto mb-3" style="width:<?php echo $loginLogoSize; ?>px;height:<?php echo $loginLogoSize; ?>px;object-fit:contain">
                <h1 class="h3 fw-bold mb-2">Giriş Yap</h1>
                <p class="text-muted mb-4">Devam etmek için giriş türünüzü seçin.</p>
                <div class="row g-3">
                    <div class="col-sm-6"><a class="btn btn-auronova w-100 py-3" href="<?php echo SITE_URL; ?>/student/studentlogin.php"><i class="fa-solid fa-user-graduate me-2"></i>Öğrenci Girişi</a></div>
                    <div class="col-sm-6"><a class="btn btn-outline-primary w-100 py-3" href="<?php echo SITE_URL; ?>/teacher/teacherlogin.php"><i class="fa-solid fa-chalkboard-user me-2"></i>Öğretmen Girişi</a></div>
                </div>
                <p class="small text-muted mt-4 mb-0">Öğrenci hesabınız yok mu? <a href="<?php echo SITE_URL; ?>/auth/login.php?open_register=1">Kayıt olun</a></p>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
