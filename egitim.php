<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/database.php';

$db = getDBConnection();
$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $db->prepare("SELECT c.*, u.name AS teacher_name, u.avatar AS teacher_avatar,
                            (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) AS lesson_count
                     FROM courses c JOIN users u ON u.id = c.teacher_id
                     WHERE c.id = :id AND c.status = 'published' LIMIT 1");
$stmt->execute([':id' => $courseId ?: 0]);
$course = $stmt->fetch();

if (!$course) {
    http_response_code(404);
    $pageTitle = 'Eğitim bulunamadı — Auronova Akademi';
} else {
    $pageTitle = $course['title'] . ' — Auronova Akademi';
}

$lessons = [];
$isEnrolled = false;
if ($course) {
    $lessonStmt = $db->prepare('SELECT title FROM lessons WHERE course_id = :course_id ORDER BY sort_order, id');
    $lessonStmt->execute([':course_id' => $course['id']]);
    $lessons = $lessonStmt->fetchAll(PDO::FETCH_COLUMN);

    $user = currentUser();
    if ($user && $user['role'] === 'student') {
        $enrollmentStmt = $db->prepare('SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id LIMIT 1');
        $enrollmentStmt->execute([':student_id' => $user['id'], ':course_id' => $course['id']]);
        $isEnrolled = (bool)$enrollmentStmt->fetchColumn();
    }
}

$csrfToken = generateCsrfToken();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="main-content">
    <?php if (!$course): ?>
        <section class="container py-5 text-center"><i class="fa-solid fa-circle-exclamation display-4 text-muted"></i><h1 class="h3 fw-bold mt-3">Eğitim bulunamadı</h1><p class="text-muted">Bu eğitim yayında olmayabilir veya bağlantı hatalı olabilir.</p><a href="<?php echo SITE_URL; ?>/egitimler.php" class="btn btn-auronova">Eğitim Kataloğuna Dön</a></section>
    <?php else: ?>
        <section class="bg-gradient-primary text-white py-5">
            <div class="container py-3">
                <a href="<?php echo SITE_URL; ?>/egitimler.php" class="text-white text-decoration-none small"><i class="fa-solid fa-arrow-left me-2"></i>Eğitim Kataloğu</a>
                <div class="mt-4"><span class="badge bg-light text-primary mb-3"><?php echo htmlspecialchars($course['category'] ?: 'Genel'); ?></span><h1 class="display-5 fw-bold"><?php echo htmlspecialchars($course['title']); ?></h1><p class="lead mb-0"><?php echo htmlspecialchars($course['description'] ?? ''); ?></p></div>
            </div>
        </section>
        <section class="container py-5">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card card-custom border-0 p-4 p-md-5 mb-4">
                        <h2 class="h4 fw-bold mb-3">Eğitim hakkında</h2>
                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($course['description'] ?: 'Bu eğitim için henüz açıklama eklenmemiş.')); ?></p>
                    </div>
                    <div class="card card-custom border-0 p-4 p-md-5">
                        <h2 class="h4 fw-bold mb-3">Eğitim içeriği <span class="text-muted fs-6 fw-normal">(<?php echo count($lessons); ?> ders)</span></h2>
                        <?php if (!$lessons): ?><p class="text-muted mb-0">Ders içerikleri hazırlanıyor.</p><?php else: ?>
                            <ol class="list-group list-group-numbered list-group-flush">
                                <?php foreach ($lessons as $lessonTitle): ?><li class="list-group-item px-0"><i class="fa-solid fa-circle-play text-primary me-2"></i><?php echo htmlspecialchars($lessonTitle); ?></li><?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </div>
                </div>
                <aside class="col-lg-4">
                    <div class="card card-custom border-0 p-4 sticky-top" style="top:100px">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <?php if (!empty($course['teacher_avatar'])): ?><img src="<?php echo SITE_URL . '/' . htmlspecialchars($course['teacher_avatar']); ?>" alt="" class="rounded-circle" style="width:56px;height:56px;object-fit:cover"><?php else: ?><div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px"><i class="fa-solid fa-user-tie fs-4"></i></div><?php endif; ?>
                            <div><div class="small text-muted">Eğitmen</div><div class="fw-bold"><?php echo htmlspecialchars($course['teacher_name']); ?></div></div>
                        </div>
                        <div class="d-flex justify-content-between border-top border-bottom py-3 mb-3"><span class="text-muted">Ders sayısı</span><strong><?php echo (int)$course['lesson_count']; ?></strong></div>
                        <?php if ($isEnrolled): ?>
                            <a class="btn btn-auronova w-100" href="<?php echo SITE_URL; ?>/student/lesson.php?course_id=<?php echo (int)$course['id']; ?>"><i class="fa-solid fa-play me-2"></i>Eğitime Devam Et</a>
                        <?php elseif (isLoggedIn() && currentUser()['role'] === 'student'): ?>
                            <form method="POST" action="<?php echo SITE_URL; ?>/student/courses.php"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>"><input type="hidden" name="enroll_course_id" value="<?php echo (int)$course['id']; ?>"><button class="btn btn-auronova w-100" type="submit"><i class="fa-solid fa-plus me-2"></i>Eğitime Katıl</button></form>
                        <?php elseif (!isLoggedIn()): ?>
                            <a class="btn btn-auronova w-100" href="<?php echo SITE_URL; ?>/auth/login.php?portal=student&amp;return_to=<?php echo rawurlencode('/egitim.php?id=' . (int)$course['id']); ?>"><i class="fa-solid fa-right-to-bracket me-2"></i>Giriş Yapıp Katıl</a>
                        <?php else: ?>
                            <a class="btn btn-outline-primary w-100" href="<?php echo SITE_URL; ?>/<?php echo currentUser()['role'] === 'teacher' ? 'teacher' : 'admin'; ?>/dashboard.php">Panele Git</a>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
