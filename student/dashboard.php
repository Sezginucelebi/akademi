<?php
$pageTitle = "Öğrenci Paneli — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');
$user = currentUser();
$db = getDBConnection();

// Fetch enrolled courses for the student
$stmt = $db->prepare("
    SELECT c.*, u.name as teacher_name, e.progress_percent, e.enrolled_at,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON c.teacher_id = u.id
    WHERE e.student_id = :student_id
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([':student_id' => $user['id']]);
$enrolledCourses = $stmt->fetchAll();

$totalEnrolled = count($enrolledCourses);
?>

<div class="container py-4 main-content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold text-dark mb-1">Öğrenci Portalı</h2>
            <p class="text-muted mb-0">Hoş geldin <strong><?php echo htmlspecialchars($user['name']); ?></strong>, öğrenim yolculuğuna devam et!</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?php echo SITE_URL; ?>/student/courses.php" class="btn btn-auronova shadow-sm">
                <i class="fa-solid fa-compass me-2"></i>Yeni Dersleri Keşfet
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-custom p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Kayıtlı Derslerim</div>
                        <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalEnrolled; ?></div>
                    </div>
                    <div class="bg-primary text-white rounded-3 p-3">
                        <i class="fa-solid fa-book-open fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Devam Eden Dersler</div>
                        <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalEnrolled; ?></div>
                    </div>
                    <div class="bg-success text-white rounded-3 p-3">
                        <i class="fa-solid fa-spinner fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Tamamlanan Ödevler</div>
                        <div class="fs-3 fw-bold text-dark mt-1">0</div>
                    </div>
                    <div class="bg-info text-white rounded-3 p-3">
                        <i class="fa-solid fa-square-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Courses -->
    <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Kayıtlı Derslerim</h4>

    <?php if (empty($enrolledCourses)): ?>
        <div class="card card-custom p-5 text-center bg-white">
            <i class="fa-solid fa-folder-open fs-1 text-muted mb-3"></i>
            <h5>Henüz hiçbir derse kayıt olmadınız.</h5>
            <p class="text-muted">Ders kataloğuna göz atarak ilginizi çeken bir eğitime kayıt olabilirsiniz.</p>
            <div class="mt-2">
                <a href="<?php echo SITE_URL; ?>/student/courses.php" class="btn btn-auronova">Ders Kataloğuna Git</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($enrolledCourses as $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom h-100 bg-white">
                        <div class="bg-gradient-primary text-white p-4 rounded-top position-relative d-flex align-items-center justify-content-center" style="height: 140px;">
                            <i class="fa-solid fa-laptop-code display-4 opacity-50"></i>
                            <span class="badge bg-light text-primary position-absolute top-0 end-0 m-3 shadow-sm"><?php echo htmlspecialchars($course['category']); ?></span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted small flex-grow-1 mb-3">
                                <?php echo htmlspecialchars(mb_substr($course['description'] ?? '', 0, 100, 'UTF-8')) . '...'; ?>
                            </p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>İlerleme</span>
                                    <span>%<?php echo (int)$course['progress_percent']; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo (int)$course['progress_percent']; ?>%" aria-valuenow="<?php echo (int)$course['progress_percent']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <span class="small text-muted"><i class="fa-solid fa-user-tie me-1"></i><?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                <a href="<?php echo SITE_URL; ?>/student/lesson.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-auronova px-3">
                                    Derslere Git <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
