<?php
$pageTitle = "Derse Konu / Video Ekle — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('teacher');
$user = currentUser();
$db = getDBConnection();

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Fetch course details & ensure it belongs to this teacher
$stmt = $db->prepare("SELECT * FROM courses WHERE id = :id AND teacher_id = :teacher_id");
$stmt->execute([':id' => $courseId, ':teacher_id' => $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    setFlashMessage('danger', 'Geçersiz ders veya bu derse erişim yetkiniz yok.');
    header("Location: " . SITE_URL . "/teacher/dashboard.php");
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('danger', 'Geçersiz İstek (CSRF Hatası).');
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $videoUrl = sanitize($_POST['video_url'] ?? '');
        $liveLink = sanitize($_POST['live_link'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 1);

        if (empty($title)) {
            setFlashMessage('danger', 'Lütfen konu başlığını giriniz.');
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO lessons (course_id, title, content, video_url, live_link, sort_order)
                    VALUES (:course_id, :title, :content, :video_url, :live_link, :sort_order)
                ");
                $stmt->execute([
                    ':course_id' => $courseId,
                    ':title' => $title,
                    ':content' => $content,
                    ':video_url' => $videoUrl,
                    ':live_link' => $liveLink,
                    ':sort_order' => $sortOrder
                ]);

                setFlashMessage('success', 'Ders konusu/videosu başarıyla eklendi!');
                header("Location: " . SITE_URL . "/teacher/add_lesson.php?course_id=" . $courseId);
                exit;
            } catch (Exception $e) {
                setFlashMessage('danger', 'Ders eklenirken hata oluştu: ' . $e->getMessage());
            }
        }
    }
}

// Fetch existing lessons
$stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = :course_id ORDER BY sort_order ASC, id ASC");
$stmt->execute([':course_id' => $courseId]);
$existingLessons = $stmt->fetchAll();

$csrfToken = generateCsrfToken();
?>

<div class="container py-4 main-content">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-custom p-4 bg-white shadow-sm border-0 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <a href="<?php echo SITE_URL; ?>/teacher/dashboard.php" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
                    <h4 class="fw-bold text-dark m-0">Derse Konu & Video Ekle: <span class="text-primary"><?php echo htmlspecialchars($course['title']); ?></span></h4>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label font-weight-medium">Konu Başlığı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required placeholder="ör. 1.1 Değişkenler ve Veri Tipleri">
                        </div>
                        <div class="col-md-4">
                            <label for="sort_order" class="form-label font-weight-medium">Sıralama Numarası</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo count($existingLessons) + 1; ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="video_url" class="form-label font-weight-medium"><i class="fa-brands fa-youtube me-1 text-danger"></i>Video Linki (YouTube Embed / Link)</label>
                            <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                        </div>

                        <div class="col-md-6">
                            <label for="live_link" class="form-label font-weight-medium"><i class="fa-solid fa-video me-1 text-success"></i>Canlı Ders Linki (Meet / Zoom / Teams)</label>
                            <input type="url" class="form-control" id="live_link" name="live_link" placeholder="https://meet.google.com/xyz-abc-def">
                        </div>

                        <div class="col-12">
                            <label for="content" class="form-label font-weight-medium">Ders Notları & Yazılı Açıklama</label>
                            <textarea class="form-control" id="content" name="content" rows="4" placeholder="Bu ders konusuna ait notlar, kod parçacıkları veya yönlendirmeler..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-auronova px-4"><i class="fa-solid fa-plus me-2"></i>Konuyu Ekle</button>
                    </div>
                </form>
            </div>

            <!-- Existing Lessons List -->
            <div class="card card-custom p-4 bg-white shadow-sm border-0">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check me-2 text-primary"></i>Eklenmiş Ders Konuları (<?php echo count($existingLessons); ?>)</h5>

                <?php if (empty($existingLessons)): ?>
                    <p class="text-muted mb-0">Bu derse henüz bir konu veya video eklenmedi.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Konu Başlığı</th>
                                    <th>Video</th>
                                    <th>Canlı Ders</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($existingLessons as $lesson): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo $lesson['sort_order']; ?></span></td>
                                        <td class="fw-medium text-dark"><?php echo htmlspecialchars($lesson['title']); ?></td>
                                        <td>
                                            <?php if (!empty($lesson['video_url'])): ?>
                                                <span class="badge bg-danger"><i class="fa-brands fa-youtube me-1"></i>Var</span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($lesson['live_link'])): ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-video me-1"></i>Aktif</span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted"><?php echo formatDate($lesson['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
