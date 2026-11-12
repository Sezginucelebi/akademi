<?php
$pageTitle = "Ders Kataloğu — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');
$user = currentUser();
$db = getDBConnection();

// Handle enrollment request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_course_id'])) {
    $courseId = (int)$_POST['enroll_course_id'];
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (verifyCsrfToken($csrfToken)) {
        try {
            $chk = $db->prepare("SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id LIMIT 1");
            $chk->execute([':student_id' => $user['id'], ':course_id' => $courseId]);
            $alreadyEnrolled = $chk->fetch();

            if (!$alreadyEnrolled) {
                $stmt = $db->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)");
                $stmt->execute([':student_id' => $user['id'], ':course_id' => $courseId]);

                // Fetch course title
                $cStmt = $db->prepare("SELECT title FROM courses WHERE id = :id LIMIT 1");
                $cStmt->execute([':id' => $courseId]);
                $course = $cStmt->fetch();
                $courseTitle = $course ? $course['title'] : 'Bilinmeyen Ders';

                // Send Telegram Notification
                $msg = "<b>📚 Öğrenci Derse Kayıt Oldu!</b>\n\n";
                $msg .= "👤 Öğrenci: " . htmlspecialchars($user['name']) . "\n";
                $msg .= "✉️ E-Posta: " . htmlspecialchars($user['email']) . "\n";
                $msg .= "📖 Kurs / Ders: " . htmlspecialchars($courseTitle) . "\n";
                $msg .= "🕒 Tarih: " . date('d.m.Y H:i');
                sendTelegramNotification($msg);
            }

            setFlashMessage('success', 'Derse başarıyla kayıt oldunuz!');
            header("Location: " . SITE_URL . "/student/lesson.php?course_id=" . $courseId);
            exit;
        } catch (Exception $e) {
            setFlashMessage('danger', 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }
}

// Fetch published courses and check if student is enrolled
$stmt = $db->prepare("
    SELECT c.*, u.name as teacher_name,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND student_id = :student_id) as is_enrolled
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    WHERE c.status = 'published'
    ORDER BY c.created_at DESC
");
$stmt->execute([':student_id' => $user['id']]);
$courses = $stmt->fetchAll();

$csrfToken = generateCsrfToken();
?>

<div class="container py-4 main-content">
    <div class="mb-4 pb-2 border-bottom">
        <h2 class="fw-bold text-dark mb-1">Ders Kataloğu</h2>
        <p class="text-muted mb-0">Auronova Akademi bünyesinde sunulan çevrimiçi eğitimleri keşfedin ve katılın.</p>
    </div>

    <?php if (empty($courses)): ?>
        <div class="alert alert-info border-0 shadow-sm p-4">
            <i class="fa-solid fa-circle-info me-2"></i>Henüz yayınlanmış bir ders bulunmamaktadır.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom h-100 bg-white">
                        <div class="bg-gradient-primary text-white p-4 rounded-top position-relative d-flex align-items-center justify-content-center" style="height: 150px;">
                            <i class="fa-solid fa-graduation-cap display-3 opacity-50"></i>
                            <span class="badge bg-light text-primary position-absolute top-0 end-0 m-3 shadow-sm"><?php echo htmlspecialchars($course['category']); ?></span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted small flex-grow-1 mb-3">
                                <?php echo htmlspecialchars($course['description'] ?? 'Eğitim açıklaması bulunmuyor.'); ?>
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                                <span><i class="fa-solid fa-video me-1 text-primary"></i><?php echo $course['total_lessons']; ?> Ders</span>
                                <span><i class="fa-solid fa-user-tie me-1 text-secondary"></i><?php echo htmlspecialchars($course['teacher_name']); ?></span>
                            </div>

                            <div class="pt-3 border-top">
                                <?php if ($course['is_enrolled'] > 0): ?>
                                    <a href="<?php echo SITE_URL; ?>/student/lesson.php?course_id=<?php echo $course['id']; ?>" class="btn btn-outline-primary w-100 fw-medium">
                                        <i class="fa-solid fa-play me-2"></i>Derslere Git
                                    </a>
                                <?php else: ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="enroll_course_id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-auronova w-100 fw-medium">
                                            <i class="fa-solid fa-plus me-2"></i>Derse Kayıt Ol
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
