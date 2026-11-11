<?php
$pageTitle = "Şifremi Unuttum — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz istek (CSRF hatası).');
    } else {
        $email = sanitize($_POST['email'] ?? '');
        if (empty($email)) {
            setFlashMessage('danger', 'Lütfen e-posta adresinizi giriniz.');
        } else {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $del = $db->prepare("DELETE FROM password_resets WHERE email = :email");
                $del->execute([':email' => $email]);

                $ins = $db->prepare("INSERT INTO password_resets (email, token) VALUES (:email, :token)");
                $ins->execute([':email' => $email, ':token' => $token]);

                $resetLink = SITE_URL . "/auth/reset_password.php?token=" . $token;
                setFlashMessage('success', 'Şifre sıfırlama bağlantısı oluşturuldu.');
            } else {
                setFlashMessage('danger', 'Bu e-posta adresine kayıtlı bir kullanıcı bulunamadı.');
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Şifremi Unuttum</h3>
                    <p class="text-muted small">E-posta adresinizi girerek yeni şifre oluşturma bağlantısı alın.</p>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label font-weight-medium">E-Posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="ornek@domain.com">
                    </div>

                    <button type="submit" class="btn btn-auronova w-100 py-2 fs-6 mb-3 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i>Sıfırlama Bağlantısı Gönder
                    </button>
                </form>

                <?php if (!empty($resetLink)): ?>
                    <div class="alert alert-success mt-3 small">
                        <strong>Şifre Sıfırlama Bağlantısı:</strong><br>
                        <a href="<?php echo $resetLink; ?>" class="alert-link text-break"><?php echo $resetLink; ?></a>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i>Giriş sayfasına dön</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
