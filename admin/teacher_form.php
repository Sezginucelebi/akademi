<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$db = getDBConnection();
$teacherId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$teacher = null;
if ($teacherId > 0) {
    $teacherQuery = $db->prepare("SELECT id, name, email, phone, bio, expertise FROM users WHERE id = :id AND role = 'teacher' LIMIT 1");
    $teacherQuery->execute([':id' => $teacherId]);
    $teacher = $teacherQuery->fetch();
    if (!$teacher) {
        setFlashMessage('danger', 'Düzenlenecek eğitmen bulunamadı.');
        header('Location: ' . SITE_URL . '/admin/dashboard.php#teachers');
        exit;
    }
}

$formName = $teacher['name'] ?? '';
$formEmail = $teacher['email'] ?? '';
$formPhone = $teacher['phone'] ?? '';
$formBio = $teacher['bio'] ?? '';
$formExpertise = $teacher['expertise'] ?? '';
$formPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formNameInput = $_POST['name'] ?? '';
    $formEmailInput = $_POST['email'] ?? '';
    $formPhoneInput = $_POST['phone'] ?? '';
    $formBioInput = $_POST['bio'] ?? '';
    $formExpertiseInput = $_POST['expertise'] ?? '';
    $formPasswordInput = $_POST['password'] ?? '';
    $formName = is_string($formNameInput) ? trim(strip_tags($formNameInput)) : '';
    $formEmail = is_string($formEmailInput) ? trim($formEmailInput) : '';
    $formPhone = is_string($formPhoneInput) ? trim(strip_tags($formPhoneInput)) : '';
    $formBio = is_string($formBioInput) ? trim(strip_tags($formBioInput)) : '';
    $formExpertise = is_string($formExpertiseInput) ? trim(strip_tags($formExpertiseInput)) : '';
    $formPassword = is_string($formPasswordInput) ? $formPasswordInput : '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'İstek doğrulanamadı. Lütfen tekrar deneyin.');
    } elseif ($formName === '' || $formEmail === '') {
        setFlashMessage('danger', 'Ad soyad ve e-posta alanları zorunludur.');
    } elseif (!filter_var($formEmail, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('danger', 'Geçerli bir e-posta adresi girin.');
    } elseif (mb_strlen($formBio, 'UTF-8') > 3000) {
        setFlashMessage('danger', 'Öz geçmiş en fazla 3000 karakter olabilir.');
    } elseif (mb_strlen($formExpertise, 'UTF-8') > 500) {
        setFlashMessage('danger', 'Uzmanlık alanları en fazla 500 karakter olabilir.');
    } elseif ((!$teacher && strlen($formPassword) < 8) || ($formPassword !== '' && strlen($formPassword) < 8)) {
        setFlashMessage('danger', 'Şifre en az 8 karakter olmalıdır.');
    } else {
        $duplicateQuery = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $duplicateQuery->execute([':email' => $formEmail, ':id' => $teacherId]);
        if ($duplicateQuery->fetch()) {
            setFlashMessage('danger', 'Bu e-posta adresi başka bir hesapta kullanılıyor.');
        } else {
            try {
                if ($teacher) {
                    if ($formPassword !== '') {
                        $save = $db->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, bio = :bio, expertise = :expertise, password = :password WHERE id = :id AND role = 'teacher'");
                        $save->execute([':name' => $formName, ':email' => $formEmail, ':phone' => $formPhone, ':bio' => $formBio, ':expertise' => $formExpertise, ':password' => password_hash($formPassword, PASSWORD_DEFAULT), ':id' => $teacherId]);
                    } else {
                        $save = $db->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, bio = :bio, expertise = :expertise WHERE id = :id AND role = 'teacher'");
                        $save->execute([':name' => $formName, ':email' => $formEmail, ':phone' => $formPhone, ':bio' => $formBio, ':expertise' => $formExpertise, ':id' => $teacherId]);
                    }
                    setFlashMessage('success', 'Eğitmen bilgileri güncellendi.');
                } else {
                    $save = $db->prepare("INSERT INTO users (name, email, phone, bio, expertise, password, role, status) VALUES (:name, :email, :phone, :bio, :expertise, :password, 'teacher', 1)");
                    $save->execute([':name' => $formName, ':email' => $formEmail, ':phone' => $formPhone, ':bio' => $formBio, ':expertise' => $formExpertise, ':password' => password_hash($formPassword, PASSWORD_DEFAULT)]);
                    setFlashMessage('success', 'Eğitmen hesabı oluşturuldu.');
                }
                header('Location: ' . SITE_URL . '/admin/dashboard.php#teachers');
                exit;
            } catch (Exception $e) {
                setFlashMessage('danger', 'Eğitmen kaydedilirken bir hata oluştu.');
            }
        }
    }
}

$pageTitle = ($teacher ? 'Eğitmeni Düzenle' : 'Eğitmen Ekle') . ' — Auronova Akademi';
$csrfToken = generateCsrfToken();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<main class="container py-4 main-content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card card-custom p-4 p-md-5 bg-white border-0">
                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                    <a href="<?php echo SITE_URL; ?>/admin/dashboard.php#teachers" class="btn btn-sm btn-light border" aria-label="Geri"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="h4 fw-bold m-0"><?php echo $teacher ? 'Eğitmeni Düzenle' : 'Yeni Eğitmen Ekle'; ?></h1>
                </div>
                <form method="POST" action="<?php echo SITE_URL; ?>/admin/teacher_form.php<?php echo $teacher ? '?id=' . (int)$teacher['id'] : ''; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <div class="mb-3"><label for="name" class="form-label">Ad Soyad</label><input class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($formName); ?>" maxlength="100" required></div>
                    <div class="mb-3"><label for="email" class="form-label">E-posta</label><input class="form-control" id="email" name="email" type="email" value="<?php echo htmlspecialchars($formEmail); ?>" maxlength="150" required></div>
                    <div class="mb-3"><label for="phone" class="form-label">Telefon <span class="text-muted small">(isteğe bağlı)</span></label><input class="form-control" id="phone" name="phone" type="tel" value="<?php echo htmlspecialchars($formPhone ?? ''); ?>" maxlength="50"></div>
                    <div class="mb-3"><label for="bio" class="form-label">Öz geçmiş <span class="text-muted small">(Akademik kadroda görünmek için doldurun)</span></label><textarea class="form-control" id="bio" name="bio" rows="5" maxlength="3000" placeholder="Uzmanlık alanlarınızı ve deneyiminizi kısaca anlatın."><?php echo htmlspecialchars($formBio); ?></textarea><div class="form-text">En az 50 karakterlik öz geçmişler Akademik Kadro sayfasında listelenir.</div></div>
                    <div class="mb-4"><label for="expertise" class="form-label">Uzmanlık alanları <span class="text-muted small">(isteğe bağlı)</span></label><input class="form-control" id="expertise" name="expertise" maxlength="500" value="<?php echo htmlspecialchars($formExpertise); ?>" placeholder="Örn. PHP, Veri Bilimi, UI/UX"><div class="form-text">Birden fazla alanı virgülle ayırın.</div></div>
                    <div class="mb-4"><label for="password" class="form-label"><?php echo $teacher ? 'Yeni Şifre (değiştirmeyecekseniz boş bırakın)' : 'Başlangıç Şifresi'; ?></label><input class="form-control" id="password" name="password" type="password" minlength="8" <?php echo $teacher ? '' : 'required'; ?> autocomplete="new-password"><div class="form-text">En az 8 karakter.</div></div>
                    <div class="d-flex justify-content-end gap-2"><a href="<?php echo SITE_URL; ?>/admin/dashboard.php#teachers" class="btn btn-light border">İptal</a><button class="btn btn-auronova px-4" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Kaydet</button></div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
