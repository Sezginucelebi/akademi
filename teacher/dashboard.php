<?php
$pageTitle = "Öğretmen Paneli — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

requireRole('teacher');
$user = currentUser();
$db = getDBConnection();

// Fetch teacher's courses
$stmt = $db->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_students
    FROM courses c
    WHERE c.teacher_id = :teacher_id
    ORDER BY c.created_at DESC
");
$stmt->execute([':teacher_id' => $user['id']]);
$teacherCourses = $stmt->fetchAll();

$totalCoursesCount = count($teacherCourses);
$totalStudentsCount = array_sum(array_column($teacherCourses, 'total_students'));
?>

<div class="container py-4 main-content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold text-dark mb-1">Öğretmen Yönetim Paneli</h2>
            <p class="text-muted mb-0">Hoş geldin <strong><?php echo htmlspecialchars($user['name']); ?></strong>, derslerini ve öğrencilerini buradan yönetebilirsin.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?php echo SITE_URL; ?>/teacher/create_course.php" class="btn btn-auronova shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Yeni Ders Oluştur
            </a>
        </div>
    </div>

    <!-- Teacher Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Verdiğim Dersler</div>
                        <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalCoursesCount; ?></div>
                    </div>
                    <div class="bg-primary text-white rounded-3 p-3">
                        <i class="fa-solid fa-chalkboard-user fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-medium">Kayıtlı Toplam Öğrenci</div>
                        <div class="fs-3 fw-bold text-dark mt-1"><?php echo $totalStudentsCount; ?></div>
                    </div>
                    <div class="bg-success text-white rounded-3 p-3">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses List -->
    <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-book me-2 text-primary"></i>Derslerim ve İçerikler</h4>

    <?php if (empty($teacherCourses)): ?>
        <div class="card card-custom p-5 text-center bg-white">
            <i class="fa-solid fa-plus-circle fs-1 text-muted mb-3"></i>
            <h5>Henüz hiç ders oluşturmadınız.</h5>
            <p class="text-muted">Hemen yeni bir eğitim oluşturarak öğrencilerinize içerik ve canlı dersler sunmaya başlayın.</p>
            <div class="mt-2">
                <a href="<?php echo SITE_URL; ?>/teacher/create_course.php" class="btn btn-auronova">Yeni Ders Ekle</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($teacherCourses as $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom h-100 bg-white">
                        <div class="bg-gradient-primary text-white p-4 rounded-top position-relative d-flex align-items-center justify-content-center" style="height: 140px;">
                            <i class="fa-solid fa-laptop-code display-4 opacity-50"></i>
                            <span class="badge bg-light text-primary position-absolute top-0 end-0 m-3 shadow-sm"><?php echo htmlspecialchars($course['category']); ?></span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold text-dark mb-0"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <span class="badge bg-success text-capitalize"><?php echo htmlspecialchars($course['status']); ?></span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1 mb-3">
                                <?php echo htmlspecialchars(mb_substr($course['description'] ?? '', 0, 90, 'UTF-8')) . '...'; ?>
                            </p>

                            <div class="d-flex justify-content-between text-muted small mb-3">
                                <span><i class="fa-solid fa-video me-1 text-primary"></i><?php echo $course['total_lessons']; ?> Konu / Video</span>
                                <span><i class="fa-solid fa-user-group me-1 text-success"></i><?php echo $course['total_students']; ?> Öğrenci</span>
                            </div>

                            <div class="pt-3 border-top d-flex gap-2">
                                <a href="<?php echo SITE_URL; ?>/teacher/add_lesson.php?course_id=<?php echo $course['id']; ?>" class="btn btn-auronova btn-sm flex-fill">
                                    <i class="fa-solid fa-plus me-1"></i>Konu / Video Ekle
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
