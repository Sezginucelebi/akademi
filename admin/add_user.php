<?php
$pageTitle = "Yeni Kullanıcı Ekle — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');
$user = currentUser();
$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz İstek (CSRF Hatası).');
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize($_POST['role'] ?? 'student');

        if (empty($name) || empty($email) || empty($password)) {
            setFlashMessage('danger', 'Lütfen tüm zorunlu alanları doldurunuz.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('danger', 'Geçerli bir e-posta adresi giriniz.');
        } else {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    INSERT INTO users (name, email, password, role, status)
                    VALUES (:name, :email, :password, :role, 1)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':role' => in_array($role, ['student', 'teacher']) ? $role : 'student',
                ]);

                setFlashMessage('success', 'Yeni kullanıcı (' . ($role === 'teacher' ? 'Öğretmen' : 'Öğrenci') . ') başarıyla eklendi!');
                header("Location: " . SITE_URL . "/admin/dashboard.php");
                exit;
            } catch (Exception $e) {
                setFlashMessage('danger', 'Kullanıcı eklenirken hata oluştu: ' . $e->getMessage());
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="container py-4 main-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card card-custom p-4 bg-white shadow-sm border-0">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
                    <h4 class="fw-bold text-dark m-0">Yeni Öğrenci veya Öğretmen Ekle</h4>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label font-weight-medium">Ad Soyad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="ör. Mehmet Demir">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label font-weight-medium">E-Posta Adresi <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="ornek@auronovasoftware.com.tr">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label font-weight-medium">Şifre <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Geçici veya kalıcı şifre">
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label font-weight-medium">Kullanıcı Rolü <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role">
                            <option value="student">Öğrenci</option>
                            <option value="teacher">Öğretmen (Eğitmen)</option>
                        </select>
                        <div class="form-text small text-muted mt-1">Öğretmen seçilirse eğitmen yetkileriyle ders oluşturabilir; öğrenci seçilirse kurslara katılım sağlayabilir.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="btn btn-light border">İptal</a>
                        <button type="submit" class="btn btn-auronova px-4"><i class="fa-solid fa-user-plus me-2"></i>Kullanıcıyı Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
