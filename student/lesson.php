<?php
$pageTitle = "Ders İzleme — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');
$user = currentUser();
$db = getDBConnection();

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;

// Fetch course details & enrollment check
$stmt = $db->prepare("
    SELECT c.*, u.name as teacher_name
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE c.id = :course_id AND e.student_id = :student_id
");
$stmt->execute([':course_id' => $courseId, ':student_id' => $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    setFlashMessage('warning', 'Bu derse kayıtlı değilsiniz veya ders bulunamadı.');
    header("Location: " . SITE_URL . "/student/courses.php");
    exit;
}

// Fetch lessons list for this course
$stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = :course_id ORDER BY sort_order ASC, id ASC");
$stmt->execute([':course_id' => $courseId]);
$lessons = $stmt->fetchAll();

// Determine active lesson
$activeLesson = null;
if (!empty($lessons)) {
    if ($lessonId > 0) {
        foreach ($lessons as $l) {
            if ($l['id'] === $lessonId) {
                $activeLesson = $l;
                break;
            }
        }
    }
    if (!$activeLesson) {
        $activeLesson = $lessons[0];
    }
}
?>

<div class="container-fluid py-4 main-content">
    <div class="row g-4">
        <!-- Sidebar: Lesson Navigation -->
        <div class="col-lg-4 col-xl-3">
            <div class="card card-custom p-3 bg-white sticky-top" style="top: 80px; z-index: 10;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <a href="<?php echo SITE_URL; ?>/student/dashboard.php" class="btn btn-sm btn-light border"><i class="fa-solid fa-arrow-left"></i></a>
                    <h6 class="fw-bold text-dark m-0 text-truncate"><?php echo htmlspecialchars($course['title']); ?></h6>
                </div>

                <div class="text-muted small mb-2"><i class="fa-solid fa-layer-group me-1"></i>Ders Müfredatı (<?php echo count($lessons); ?> Konu)</div>

                <?php if (empty($lessons)): ?>
                    <div class="alert alert-warning small my-2">Henüz ders içeriği eklenmemiştir.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <?php $isActive = ($activeLesson && $activeLesson['id'] === $lesson['id']); ?>
                            <a href="?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $lesson['id']; ?>"
                               class="list-group-item list-group-item-action border-0 rounded-3 mb-1 d-flex align-items-center justify-content-between p-2.5 <?php echo $isActive ? 'bg-primary text-white fw-semibold' : 'text-dark'; ?>">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="badge <?php echo $isActive ? 'bg-white text-primary' : 'bg-light text-secondary'; ?> rounded-circle" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                        <?php echo $index + 1; ?>
                                    </span>
                                    <span class="text-truncate small"><?php echo htmlspecialchars($lesson['title']); ?></span>
                                </div>
                                <?php if (!empty($lesson['live_link'])): ?>
                                    <span class="badge bg-danger ms-1 small" title="Canlı Ders"><i class="fa-solid fa-video"></i></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Video & Content Viewer -->
        <div class="col-lg-8 col-xl-9">
            <?php if ($activeLesson): ?>
                <div class="card card-custom p-4 bg-white mb-4">
                    <h3 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($activeLesson['title']); ?></h3>

                    <!-- Live Lesson Alert Banner -->
                    <?php if (!empty($activeLesson['live_link'])): ?>
                        <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-3 shadow-sm mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-tower-broadcast fs-4 text-danger animate-pulse"></i>
                                <div>
                                    <strong class="d-block">Canlı Ders Bağlantısı Aktif!</strong>
                                    <span class="small">Eğitmeniniz canlı yayın odasını başlattı veya planladı.</span>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($activeLesson['live_link']); ?>" target="_blank" class="btn btn-danger btn-sm px-3 fw-medium">
                                <i class="fa-solid fa-video me-1"></i>Canlı Derse Katıl
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Video Embed -->
                    <?php if (!empty($activeLesson['video_url'])): ?>
                        <?php $embedUrl = getYouTubeEmbedUrl($activeLesson['video_url']); ?>
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm mb-4 bg-black">
                            <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" title="<?php echo htmlspecialchars($activeLesson['title']); ?>" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>

                    <!-- Lesson Notes & Content -->
                    <div class="border-top pt-3 mt-3">
                        <h5 class="fw-bold text-dark mb-2"><i class="fa-solid fa-file-lines me-2 text-primary"></i>Ders Notları & Açıklama</h5>
                        <div class="text-secondary leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($activeLesson['content'] ?? 'Bu ders için henüz yazılı not eklenmemiştir.')); ?>
                        </div>
                    </div>

                    <!-- Material Download -->
                    <?php if (!empty($activeLesson['file_path'])): ?>
                        <div class="border-top pt-3 mt-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-paperclip me-2 text-primary"></i>Ders Materyali</h6>
                            <a href="<?php echo htmlspecialchars($activeLesson['file_path']); ?>" download class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-download me-1"></i>Ek Dosyayı İndir
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card card-custom p-5 text-center bg-white">
                    <i class="fa-solid fa-book-open fs-1 text-muted mb-3"></i>
                    <h5>Lütfen sol taraftaki menüden bir ders konusu seçin.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
