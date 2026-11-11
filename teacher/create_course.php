<?php
$pageTitle = "Yeni Ders Oluştur — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('teacher');
$user = currentUser();
$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz İstek (CSRF Hatası).');
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $category = sanitize($_POST['category'] ?? 'Genel');
        $description = sanitize($_POST['description'] ?? '');
        $status = sanitize($_POST['status'] ?? 'published');

        if (empty($title)) {
            setFlashMessage('danger', 'Lütfen ders başlığını giriniz.');
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO courses (teacher_id, title, category, description, status)
                    VALUES (:teacher_id, :title, :category, :description, :status)
                ");
                $stmt->execute([
                    ':teacher_id' => $user['id'],
                    ':title' => $title,
                    ':category' => $category,
                    ':description' => $description,
                    ':status' => $status,
                ]);
                $newCourseId = $db->lastInsertId();

                setFlashMessage('success', 'Yeni ders başarıyla oluşturuldu! Şimdi konuları ekleyebilirsiniz.');
                header("Location: " . SITE_URL . "/teacher/add_lesson.php?course_id=" . $newCourseId);
                exit;
            } catch (Exception $e) {
                setFlashMessage('danger', 'Ders oluşturulurken hata oluştu: ' . $e->getMessage());
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
                    <a href="<?php echo SITE_URL; ?>/teacher/dashboard.php" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
                    <h4 class="fw-bold text-dark m-0">Yeni Ders Oluştur</h4>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="mb-3">
                        <label for="title" class="form-label font-weight-medium">Ders Başlığı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="ör. Python ile Yapay Zeka ve Veri Bilimi">
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label font-weight-medium">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="Yazılım & Programlama">Yazılım & Programlama</option>
                            <option value="Tasarım & UI/UX">Tasarım & UI/UX</option>
                            <option value="Siber Güvenlik">Siber Güvenlik</option>
                            <option value="Veri Bilimi">Veri Bilimi</option>
                            <option value="Pazarlama & İş">Pazarlama & İş</option>
                            <option value="Genel">Genel</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label font-weight-medium">Ders Açıklaması</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Bu derste nelerin öğretileceği ve hedefleri..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label font-weight-medium">Yayın Durumu</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published">Yayınla (Öğrenciler görebilir)</option>
                            <option value="draft">Taslak (Gizli)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo SITE_URL; ?>/teacher/dashboard.php" class="btn btn-light border">İptal</a>
                        <button type="submit" class="btn btn-auronova px-4"><i class="fa-solid fa-check me-2"></i>Dersi Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
