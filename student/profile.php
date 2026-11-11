<?php
$pageTitle = "Profil Ayarları — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');
$currentUserData = currentUser();
$db = getDBConnection();

// Fetch latest user info from DB
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $currentUserData['id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: " . SITE_URL . "/auth/logout.php");
    exit;
}

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz istek (CSRF doğrulama hatası).');
        header("Location: " . SITE_URL . "/student/profile.php");
        exit;
    }

    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($name)) {
            setFlashMessage('danger', 'Ad ve soyad alanı boş bırakılamaz.');
        } else {
            // Avatar upload
            $avatarPath = $user['avatar'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($fileExt, $allowedExts)) {
                    $uploadDir = __DIR__ . '/../assets/img/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $newFileName = 'avatar_' . $user['id'] . '_' . time() . '.' . $fileExt;
                    $destination = $uploadDir . $newFileName;
                    if (move_uploaded_file($fileTmp, $destination)) {
                        // Delete old avatar if exists
                        if (!empty($user['avatar']) && file_exists(__DIR__ . '/../' . $user['avatar'])) {
                            @unlink(__DIR__ . '/../' . $user['avatar']);
                        }
                        $avatarPath = 'assets/img/avatars/' . $newFileName;
                    }
                }
            }

            $upd = $db->prepare("UPDATE users SET name = :name, phone = :phone, avatar = :avatar WHERE id = :id");
            $upd->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':avatar' => $avatarPath,
                ':id' => $user['id']
            ]);

            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_avatar'] = $avatarPath;

            setFlashMessage('success', 'Profil bilgileriniz başarıyla güncellendi.');
            header("Location: " . SITE_URL . "/student/profile.php");
            exit;
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            setFlashMessage('danger', 'Lütfen tüm şifre alanlarını doldurun.');
        } elseif (!password_verify($currentPassword, $user['password'])) {
            setFlashMessage('danger', 'Mevcut şifreniz hatalı.');
        } elseif (strlen($newPassword) < 6) {
            setFlashMessage('danger', 'Yeni şifre en az 6 karakter olmalıdır.');
        } elseif ($newPassword !== $confirmPassword) {
            setFlashMessage('danger', 'Yeni şifreler birbiriyle eşleşmiyor.');
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $upd->execute([':password' => $hashed, ':id' => $user['id']]);

            setFlashMessage('success', 'Şifreniz başarıyla değiştirildi.');
            header("Location: " . SITE_URL . "/student/profile.php");
            exit;
        }
    } elseif ($action === 'delete_account') {
        $confirmDelete = $_POST['confirm_delete'] ?? '';
        if ($confirmDelete === 'SIL') {
            // Delete user record (cascades to enrollments / submissions)
            $del = $db->prepare("DELETE FROM users WHERE id = :id");
            $del->execute([':id' => $user['id']]);

            // Clear session and logout
            $_SESSION = array();
            session_destroy();
            session_start();
            setFlashMessage('info', 'Hesabınız başarıyla silinmiştir.');
            header("Location: " . SITE_URL . "/auth/login.php");
            exit;
        } else {
            setFlashMessage('danger', 'Hesabınızı silmek için onay kutusuna "SIL" yazmalısınız.');
            header("Location: " . SITE_URL . "/student/profile.php");
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="container py-4 main-content">
    <div class="mb-4 pb-2 border-bottom">
        <h2 class="fw-bold text-dark mb-1">Profil ve Hesap Ayarları</h2>
        <p class="text-muted mb-0">Kişisel bilgilerinizi güncelleyebilir, şifrenizi değiştirebilir veya hesabınızı silebilirsiniz.</p>
    </div>

    <div class="row g-4">
        <!-- Update Profile Info Card -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 bg-white h-100 shadow-sm">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Kişisel Bilgiler</h5>

                <div class="text-center mb-4">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo SITE_URL . '/' . htmlspecialchars($user['avatar']); ?>" alt="Profil Fotoğrafı" class="rounded-circle shadow-sm border" style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; font-size: 36px; font-weight: 700;">
                            <?php echo mb_substr($user['name'], 0, 1, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3">
                        <label for="name" class="form-label font-weight-medium small">Adı Soyadı</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label font-weight-medium small">E-Posta Adresi (Değiştirilemez)</label>
                        <input type="email" class="form-control bg-light" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label font-weight-medium small">İletişim Bilgisi (Telefon)</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="0532 000 00 00">
                    </div>

                    <div class="mb-4">
                        <label for="avatar" class="form-label font-weight-medium small">Profil Fotoğrafı Yükle / Güncelle</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/png, image/jpeg, image/webp">
                        <div class="form-text small">JPG, PNG veya WEBP formatında görsel yükleyebilirsiniz.</div>
                    </div>

                    <button type="submit" class="btn btn-auronova w-100 py-2 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Bilgileri Güncelle
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password & Delete Account Column -->
        <div class="col-lg-6">
            <!-- Change Password Card -->
            <div class="card card-custom p-4 bg-white shadow-sm mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-key me-2 text-warning"></i>Şifre Değiştir</h5>
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label font-weight-medium small">Mevcut Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="current_password" name="current_password" required placeholder="••••••••">
                            <button class="btn btn-light border border-start-0 text-muted px-3" type="button" onclick="togglePassword('current_password', 'icon1')">
                                <i class="fa-solid fa-eye" id="icon1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label font-weight-medium small">Yeni Şifre</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="new_password" name="new_password" required placeholder="En az 6 karakter">
                            <button class="btn btn-light border border-start-0 text-muted px-3" type="button" onclick="togglePassword('new_password', 'icon2')">
                                <i class="fa-solid fa-eye" id="icon2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label font-weight-medium small">Yeni Şifre (Tekrar)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="confirm_password" name="confirm_password" required placeholder="Yeni şifrenizi tekrar girin">
                            <button class="btn btn-light border border-start-0 text-muted px-3" type="button" onclick="togglePassword('confirm_password', 'icon3')">
                                <i class="fa-solid fa-eye" id="icon3"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark w-100 py-2 fw-semibold shadow-sm">
                        <i class="fa-solid fa-shield-halved me-2"></i>Şifreyi Güncelle
                    </button>
                </form>
            </div>

            <!-- Delete Account Card -->
            <div class="card card-custom p-4 bg-white shadow-sm border border-danger">
                <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Hesabı Sil</h5>
                <p class="text-muted small">Hesabınızı sildiğinizde kayıtlı olduğunuz tüm dersler, ilerlemeleriniz ve verileriniz kalıcı olarak silinecektir.</p>

                <form action="" method="POST" onsubmit="return confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="action" value="delete_account">

                    <div class="mb-3">
                        <label for="confirm_delete" class="form-label font-weight-medium small text-danger">Onaylamak için kutuya <strong>SIL</strong> yazın:</label>
                        <input type="text" class="form-control" id="confirm_delete" name="confirm_delete" required placeholder="SIL">
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-2 shadow-sm">
                        <i class="fa-solid fa-user-xmark me-2"></i>Hesabımı Kalıcı Olarak Sil
                    </button>
                </form>
            </div>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
