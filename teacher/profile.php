<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$user = currentUser();
$db = getDBConnection();
$profileQuery = $db->prepare("SELECT bio, expertise FROM users WHERE id = :id AND role = 'teacher' LIMIT 1");
$profileQuery->execute([':id' => $user['id']]);
$profile = $profileQuery->fetch() ?: ['bio' => '', 'expertise' => ''];
$bio = $profile['bio'] ?? '';
$expertise = $profile['expertise'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bioInput = $_POST['bio'] ?? '';
    $expertiseInput = $_POST['expertise'] ?? '';
    $bio = is_string($bioInput) ? trim(strip_tags($bioInput)) : '';
    $expertise = is_string($expertiseInput) ? trim(strip_tags($expertiseInput)) : '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'İstek doğrulanamadı. Lütfen tekrar deneyin.');
    } elseif (mb_strlen($bio, 'UTF-8') < 50) {
        setFlashMessage('danger', 'Akademik Kadroda görünmek için öz geçmişiniz en az 50 karakter olmalıdır.');
    } elseif (mb_strlen($bio, 'UTF-8') > 3000) {
        setFlashMessage('danger', 'Öz geçmiş en fazla 3000 karakter olabilir.');
    } elseif (mb_strlen($expertise, 'UTF-8') > 500) {
        setFlashMessage('danger', 'Uzmanlık alanları en fazla 500 karakter olabilir.');
    } else {
        $save = $db->prepare("UPDATE users SET bio = :bio, expertise = :expertise WHERE id = :id AND role = 'teacher'");
        $save->execute([':bio' => $bio, ':expertise' => $expertise, ':id' => $user['id']]);
        setFlashMessage('success', 'Öz geçmişiniz kaydedildi. Akademik Kadro sayfasında yayınlanacaktır.');
        header('Location: ' . SITE_URL . '/teacher/profile.php');
        exit;
    }
}

$pageTitle = 'Öz Geçmişim — Auronova Akademi';
$csrfToken = generateCsrfToken();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container py-4 main-content">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom border-0 p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px"><i class="fa-solid fa-file-lines fs-4"></i></div>
                    <div><h1 class="h4 fw-bold mb-1">Öz Geçmişim</h1><p class="text-muted mb-0">Akademik Kadro sayfasında görünmek için öz geçmişinizi ekleyin.</p></div>
                </div>
                <form method="POST" action="<?php echo SITE_URL; ?>/teacher/profile.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <div class="mb-3"><label class="form-label" for="bio">Uzmanlık alanlarınız ve deneyiminiz</label><textarea class="form-control" id="bio" name="bio" rows="8" minlength="50" maxlength="3000" required placeholder="Kendinizi, uzmanlık alanlarınızı ve mesleki deneyiminizi tanıtın."><?php echo htmlspecialchars($bio); ?></textarea><div class="form-text">50–3000 karakter. Yalnızca metin olarak yayınlanır.</div></div>
                    <div class="mb-4"><label class="form-label" for="expertise">Uzmanlık alanları</label><input class="form-control" id="expertise" name="expertise" maxlength="500" value="<?php echo htmlspecialchars($expertise); ?>" placeholder="Örn. PHP, Veri Bilimi, UI/UX"><div class="form-text">İsteğe bağlıdır; birden fazla alanı virgülle ayırın.</div></div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><a href="<?php echo SITE_URL; ?>/akademik-kadro.php" class="btn btn-outline-primary"><i class="fa-solid fa-eye me-2"></i>Akademik Kadroyu Gör</a><button class="btn btn-auronova px-4" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Öz Geçmişi Kaydet</button></div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
