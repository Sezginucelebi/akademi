<?php
$pageTitle = "Yönetim Paneli — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');
$user = currentUser();
$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_teacher_id'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'İstek doğrulanamadı. Lütfen tekrar deneyin.');
    } else {
        $teacherId = (int)$_POST['delete_teacher_id'];
        try {
            $deleteTeacher = $db->prepare("DELETE FROM users WHERE id = :id AND role = 'teacher'");
            $deleteTeacher->execute([':id' => $teacherId]);
            setFlashMessage($deleteTeacher->rowCount() ? 'success' : 'warning', $deleteTeacher->rowCount() ? 'Eğitmen ve ona bağlı eğitim kayıtları silindi.' : 'Eğitmen bulunamadı.');
        } catch (Exception $e) {
            setFlashMessage('danger', 'Eğitmen silinirken bir hata oluştu.');
        }
    }
    header('Location: ' . SITE_URL . '/admin/dashboard.php#teachers');
    exit;
}

// System stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalTeachers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalCourses = $db->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// Fetch teachers, students, admins separately for clear separation
$teachers = $db->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY created_at DESC")->fetchAll();
$students = $db->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC")->fetchAll();
$admins = $db->query("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC")->fetchAll();
?>

<div class="container py-4 main-content">
    <div class="mb-4 pb-2 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">Sistem Yönetim Paneli (Admin)</h2>
            <p class="text-muted mb-0">Platform genel istatistikleri ve öğrenci / öğretmen kullanıcı yönetimi.</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/add_user.php" class="btn btn-auronova px-3">
            <i class="fa-solid fa-user-plus me-2"></i>Yeni Kullanıcı Ekle
        </a>
    </div>

    <!-- Admin Overview Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-4 border-primary">
                <div class="text-muted small fw-medium">Toplam Kullanıcı</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalUsers; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-4 border-info">
                <div class="text-muted small fw-medium">Kayıtlı Öğrenciler</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalStudents; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-4 border-success">
                <div class="text-muted small fw-medium">Öğretmen Sayısı</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalTeachers; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-4 border-warning">
                <div class="text-muted small fw-medium">Toplam Ders Sayısı</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalCourses; ?></div>
            </div>
        </div>
    </div>

    <!-- Teachers Management Section -->
    <div class="card card-custom p-4 bg-white shadow-sm border-0 mb-4" id="teachers">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-chalkboard-user me-2 text-success"></i>Öğretmenler Listesi</h5>
            <div class="d-flex align-items-center gap-2"><span class="badge bg-success"><?php echo count($teachers); ?> Eğitmen</span><a href="<?php echo SITE_URL; ?>/admin/teacher_form.php" class="btn btn-sm btn-auronova"><i class="fa-solid fa-user-plus me-1"></i>Eğitmen Ekle</a></div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Ad Soyad</th>
                        <th>E-Posta Adresi</th>
                        <th>Öz Geçmiş</th>
                        <th>Kayıt Tarihi</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">Kayıtlı öğretmen bulunmuyor.</td></tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                            <tr>
                                <td><?php echo $t['id']; ?></td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($t['name']); ?></td>
                                <td><?php echo htmlspecialchars($t['email']); ?></td>
                                <td><?php echo mb_strlen(trim($t['bio'] ?? ''), 'UTF-8') >= 50 ? '<span class="badge bg-success-subtle text-success">Eklendi</span>' : '<span class="badge bg-warning-subtle text-warning-emphasis">Eksik</span>'; ?></td>
                                <td class="small text-muted"><?php echo formatDate($t['created_at']); ?></td>
                                <td><div class="d-flex justify-content-end gap-2"><a href="<?php echo SITE_URL; ?>/admin/teacher_form.php?id=<?php echo (int)$t['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-to-square me-1"></i>Düzenle</a><form method="POST" action="<?php echo SITE_URL; ?>/admin/dashboard.php#teachers" onsubmit="return confirm('Bu eğitmeni silmek, ona bağlı kursları, dersleri ve öğrenci kayıtlarını da kalıcı olarak silecek. Devam edilsin mi?');"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>"><input type="hidden" name="delete_teacher_id" value="<?php echo (int)$t['id']; ?>"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i>Sil</button></form></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Students Management Section -->
    <div class="card card-custom p-4 bg-white shadow-sm border-0 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-user-graduate me-2 text-primary"></i>Öğrenciler Listesi</h5>
            <span class="badge bg-primary"><?php echo count($students); ?> Öğrenci</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Ad Soyad</th>
                        <th>E-Posta Adresi</th>
                        <th>Kayıt Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Kayıtlı öğrenci bulunmuyor.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($s['name']); ?></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td class="small text-muted"><?php echo formatDate($s['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admins Section -->
    <div class="card card-custom p-4 bg-white shadow-sm border-0">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-user-shield me-2 text-danger"></i>Yöneticiler (Admin)</h5>
            <span class="badge bg-danger"><?php echo count($admins); ?> Admin</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Ad Soyad</th>
                        <th>E-Posta Adresi</th>
                        <th>Kayıt Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?php echo $a['id']; ?></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($a['name']); ?></td>
                            <td><?php echo htmlspecialchars($a['email']); ?></td>
                            <td class="small text-muted"><?php echo formatDate($a['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
