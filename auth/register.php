<?php
$pageTitle = "Öğrenci Kayıt Ol — Auronova Akademi";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    $user = currentUser();
    if ($user['role'] === 'teacher') {
        header("Location: " . SITE_URL . "/teacher/dashboard.php");
    } elseif ($user['role'] === 'admin') {
        header("Location: " . SITE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . SITE_URL . "/student/dashboard.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz istek (CSRF doğrulama hatası). Lütfen tekrar deneyin.');
        header("Location: " . SITE_URL . "/auth/register.php");
        exit;
    }

    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $kvkk = isset($_POST['kvkk']) ? true : false;

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        setFlashMessage('danger', 'Lütfen zorunlu alanları doldurun.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('danger', 'Lütfen geçerli bir e-posta adresi giriniz.');
    } elseif (strlen($password) < 6) {
        setFlashMessage('danger', 'Şifreniz en az 6 karakter olmalıdır.');
    } elseif ($password !== $confirmPassword) {
        setFlashMessage('danger', 'Girdiğiniz şifreler birbiriyle eşleşmiyor.');
    } elseif (!$kvkk) {
        setFlashMessage('danger', 'Kayıt olabilmek için KVKK Aydınlatma Metnini onaylamalısınız.');
    } else {
        try {
            $db = getDBConnection();

            // Check if email exists
            $chk = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $chk->execute([':email' => $email]);
            if ($chk->fetch()) {
                setFlashMessage('danger', 'Bu e-posta adresi ile zaten kayıtlı bir hesap bulunmaktadır.');
            } else {
                $fullName = $firstName . ' ' . $lastName;
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (:name, :email, :password, :phone, 'student', 1)");
                $stmt->execute([
                    ':name' => $fullName,
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':phone' => $phone
                ]);

                $userId = $db->lastInsertId();

                // Send Telegram Notification
                $msg = "<b>Yeni Öğrenci Kaydı!</b>\n\n";
                $msg .= "👤 Ad Soyad: " . htmlspecialchars($fullName) . "\n";
                $msg .= "✉️ E-Posta: " . htmlspecialchars($email) . "\n";
                $msg .= "📞 Telefon: " . htmlspecialchars($phone ?: '-') . "\n";
                $msg .= "🕒 Tarih: " . date('d.m.Y H:i');
                sendTelegramNotification($msg);

                // Auto login
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'student';
                $_SESSION['user_avatar'] = null;

                setFlashMessage('success', 'Öğrenci hesabınız başarıyla oluşturuldu. Hoş geldiniz!');
                header("Location: " . SITE_URL . "/student/dashboard.php");
                exit;
            }
        } catch (Exception $e) {
            setFlashMessage('danger', 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
$csrfToken = generateCsrfToken();
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom p-4 p-md-5 shadow-lg border-0">
                <div class="text-center mb-4">
                    <img src="<?php echo SITE_URL; ?>/assets/img/icon.png" alt="Auronova Akademi Icon" class="mb-3 shadow-sm rounded-circle" style="width: 72px; height: 72px; object-fit: contain;">
                    <h3 class="fw-bold text-dark">Öğrenci Kayıt Formu</h3>
                    <p class="text-muted small">Auronova Akademi ailesine katılmak için bilgilerinizi doldurun</p>
                </div>

                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label font-weight-medium">Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Adınız">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label font-weight-medium">Soyadı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Soyadınız">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label font-weight-medium">E-Posta (Mail) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="ornek@domain.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label font-weight-medium">İletişim Bilgisi (Telefon)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="tel" class="form-control border-start-0 ps-0" id="phone" name="phone" placeholder="0532 000 00 00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label font-weight-medium">Şifre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="password" name="password" required placeholder="En az 6 karakter">
                                <button class="btn btn-light border border-start-0 text-muted px-2" type="button" onclick="togglePassword('password', 'toggleReg1')">
                                    <i class="fa-solid fa-eye" id="toggleReg1"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label font-weight-medium">Şifre (Tekrar) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="confirm_password" name="confirm_password" required placeholder="Şifrenizi tekrar girin">
                                <button class="btn btn-light border border-start-0 text-muted px-2" type="button" onclick="togglePassword('confirm_password', 'toggleReg2')">
                                    <i class="fa-solid fa-eye" id="toggleReg2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <script>
                    function togglePassword(inputId, iconId) {
                        const input = document.getElementById(inputId);
                        const icon = document.getElementById(iconId);
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            input.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                    </script>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="kvkk" name="kvkk" required>
                        <label class="form-check-label small text-muted" for="kvkk">
                            Kişisel verilerimin <a href="#" data-bs-toggle="modal" data-bs-target="#kvkkModal" class="text-primary text-decoration-none fw-semibold">KVKK Aydınlatma Metni</a> kapsamında işlenmesini onaylıyorum. <span class="text-danger">*</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-auronova w-100 py-2 fs-6 mb-3 shadow-sm">
                        <i class="fa-solid fa-user-plus me-2"></i>Öğrenci Olarak Kayıt Ol
                    </button>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="small text-muted mb-0">Zaten hesabınız var mı? <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-primary fw-semibold text-decoration-none">Giriş Yapın</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KVKK Modal -->
<div class="modal fade" id="kvkkModal" tabindex="-1" aria-labelledby="kvkkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="kvkkModalLabel">KVKK Aydınlatma Metni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body small text-muted">
                <p>Auronova Akademi olarak kişisel verilerinizin güvenliğine önem veriyoruz. 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, öğrencimiz olarak kaydolduğunuzda ad, soyad, e-posta, iletişim bilgisi ve profil görseliniz eğitim süreçlerinin yönetilmesi, ilerleme takibi ve sistem güvenliğinin sağlanması amacıyla işlenmektedir.</p>
                <p class="mb-0">Verileriniz üçüncü şahıslarla paylaşılmamakta olup, dilediğiniz zaman hesabınızı kapatarak verilerinizin silinmesini talep edebilirsiniz.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
