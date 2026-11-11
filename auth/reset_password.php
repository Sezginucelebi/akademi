<?php
$pageTitle = "Yeni Şifre Belirle — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$db = getDBConnection();

$validToken = false;
$resetEmail = '';

if (!empty($token)) {
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $record = $stmt->fetch();
    if ($record) {
        $validToken = true;
        $resetEmail = $record['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz istek.');
    } elseif (empty($newPassword) || strlen($newPassword) < 6) {
        setFlashMessage('danger', 'Şifre en az 6 karakter olmalıdır.');
    } elseif ($newPassword !== $confirmPassword) {
        setFlashMessage('danger', 'Şifreler eşleşmiyor.');
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $upd->execute([':password' => $hashed, ':email' => $resetEmail]);

        $del = $db->prepare("DELETE FROM password_resets WHERE token = :token");
        $del->execute([':token' => $token]);

        setFlashMessage('success', 'Şifreniz başarıyla güncellendi! Yeni şifrenizle giriş yapabilirsiniz.');
        header("Location: " . SITE_URL . "/auth/login.php");
        exit;
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Yeni Şifre Belirle</h3>
                    <p class="text-muted small">Lütfen hesabınız için güçlü bir yeni şifre girin.</p>
                </div>

                <?php if (!$validToken): ?>
                    <div class="alert alert-danger">Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.</div>
                    <div class="text-center">
                        <a href="<?php echo SITE_URL; ?>/auth/forgot_password.php" class="btn btn-auronova btn-sm">Yeni Bağlantı İste</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label font-weight-medium">Yeni Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="En az 6 karakter">
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label font-weight-medium">Yeni Şifre (Tekrar)</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Şifrenizi tekrar girin">
                        </div>

                        <button type="submit" class="btn btn-auronova w-100 py-2 fs-6 mb-3 shadow-sm">
                            <i class="fa-solid fa-key me-2"></i>Şifreyi Güncelle
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
