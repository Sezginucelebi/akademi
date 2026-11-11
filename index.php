<?php
$pageTitle = "Online Ders ve Canlı Eğitim Portalı";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

// Fetch published courses for landing showcase
$db = getDBConnection();
$stmt = $db->query("
    SELECT c.*, u.name as teacher_name,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    WHERE c.status = 'published'
    ORDER BY c.created_at DESC LIMIT 6
");
$featuredCourses = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="bg-gradient-primary text-white py-5 shadow-sm">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-3">Geleceğin Teknolojilerini Online Öğrenin!</h1>
                <p class="lead mb-4 opacity-90">
                    Yazılım, veri bilimi ve tasarım alanlarında uzman eğitmenlerden ders videoları izleyin, canlı derslere katılın ve kariyerinizi geliştirin.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (isLoggedIn()): ?>
                        <?php $u = currentUser(); ?>
                        <a href="<?php echo SITE_URL; ?>/<?php echo $u['role']; ?>/dashboard.php" class="btn btn-light btn-lg text-primary fw-bold px-4 shadow">
                            <i class="fa-solid fa-gauge me-2"></i>Panelime Git
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-light btn-lg text-primary fw-bold px-4 shadow">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Öğrenci / Öğretmen Girişi
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="fa-solid fa-laptop-code display-1 opacity-75"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-3">
                    <div class="bg-light text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-video fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Ders Videosu & Dokümanlar</h5>
                    <p class="text-muted small">İstediğiniz zaman HD kalitesinde ders içeriklerini ve eğitim materyallerini izleyin.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <div class="bg-light text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-headset fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Canlı Ders Entegrasyonu</h5>
                    <p class="text-muted small">Eğitmenlerinizin açtığı canlı ders yayınlarına tek tıkla katılarak soru sorun.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <div class="bg-light text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-chart-line fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">İlerleme Takibi</h5>
                    <p class="text-muted small">Tamamladığınız ders konularını ve eğitim sürecinizi öğrenci panelinizden takip edin.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5 main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Öne Çıkan Eğitimler</h3>
                <p class="text-muted mb-0">En son eklenen çevrimiçi dersler ve kurslar</p>
            </div>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/student/courses.php" class="btn btn-outline-primary fw-medium">Tümünü Gör <i class="fa-solid fa-arrow-right ms-1"></i></a>
            <?php endif; ?>
        </div>

        <?php if (empty($featuredCourses)): ?>
            <div class="alert alert-light border p-4 text-center text-muted">
                Henüz yayınlanmış bir ders bulunmuyor.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featuredCourses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-custom h-100 bg-white">
                            <div class="bg-gradient-primary text-white p-4 rounded-top position-relative d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fa-solid fa-graduation-cap display-4 opacity-50"></i>
                                <span class="badge bg-light text-primary position-absolute top-0 end-0 m-3 shadow-sm"><?php echo htmlspecialchars($course['category']); ?></span>
                            </div>
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text text-muted small flex-grow-1 mb-3">
                                    <?php echo htmlspecialchars(mb_substr($course['description'] ?? '', 0, 95, 'UTF-8')) . '...'; ?>
                                </p>
                                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                    <span class="small text-muted"><i class="fa-solid fa-user-tie me-1"></i><?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="<?php echo SITE_URL; ?>/student/courses.php" class="btn btn-sm btn-auronova px-3">Derse Katıl</a>
                                    <?php else: ?>
                                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-sm btn-auronova px-3">Derse Katıl</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
