<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Eğitimler — Auronova Akademi';
$db = getDBConnection();
$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) && is_string($_GET['category']) ? trim($_GET['category']) : '';

$categories = $db->query("SELECT DISTINCT category FROM courses WHERE status = 'published' AND category IS NOT NULL AND category <> '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$conditions = ["c.status = 'published'"];
$params = [];
if ($search !== '') {
    $conditions[] = '(c.title LIKE :search_title OR c.description LIKE :search_description OR u.name LIKE :search_teacher)';
    $params[':search_title'] = '%' . $search . '%';
    $params[':search_description'] = '%' . $search . '%';
    $params[':search_teacher'] = '%' . $search . '%';
}
if ($category !== '' && in_array($category, $categories, true)) {
    $conditions[] = 'c.category = :category';
    $params[':category'] = $category;
}

$query = "SELECT c.id, c.title, c.category, c.description, c.created_at, u.name AS teacher_name,
                 (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count
          FROM courses c JOIN users u ON u.id = c.teacher_id
          WHERE " . implode(' AND ', $conditions) . " ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="main-content">
    <section class="bg-gradient-primary text-white py-5">
        <div class="container py-3">
            <span class="text-uppercase small fw-semibold opacity-75">Auronova Akademi</span>
            <h1 class="display-5 fw-bold mt-2 mb-2">Eğitim Kataloğu</h1>
            <p class="lead mb-0">İlginizi çeken eğitimi bulun ve öğrenme yolculuğunuza başlayın.</p>
        </div>
    </section>
    <section class="container py-5">
        <form method="GET" action="<?php echo SITE_URL; ?>/egitimler.php" class="card card-custom border-0 p-3 p-md-4 mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6"><label class="form-label" for="course-search">Eğitim veya eğitmen ara</label><input class="form-control" id="course-search" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Örn. Python"></div>
                <div class="col-md-4"><label class="form-label" for="course-category">Kategori</label><select class="form-select" id="course-category" name="category"><option value="">Tüm kategoriler</option><?php foreach ($categories as $categoryOption): ?><option value="<?php echo htmlspecialchars($categoryOption); ?>" <?php echo $category === $categoryOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoryOption); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2 d-grid"><button class="btn btn-auronova" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Ara</button></div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">Yayınlanan Eğitimler</h2><span class="small text-muted"><?php echo count($courses); ?> eğitim</span></div>
        <?php if (!$courses): ?>
            <div class="alert alert-light border text-center p-4">Aramanızla eşleşen yayınlanmış eğitim bulunamadı.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="card card-custom h-100 border-0">
                            <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center position-relative" style="height:150px"><i class="fa-solid fa-graduation-cap display-4 opacity-50"></i><span class="badge bg-light text-primary position-absolute top-0 end-0 m-3"><?php echo htmlspecialchars($course['category'] ?: 'Genel'); ?></span></div>
                            <div class="card-body d-flex flex-column p-4">
                                <h3 class="h5 fw-bold"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="small text-muted flex-grow-1"><?php echo htmlspecialchars(mb_substr($course['description'] ?? '', 0, 150, 'UTF-8')) . (mb_strlen($course['description'] ?? '', 'UTF-8') > 150 ? '…' : ''); ?></p>
                                <div class="d-flex justify-content-between small text-muted mb-3"><span><i class="fa-solid fa-book-open me-1"></i><?php echo (int)$course['lesson_count']; ?> ders</span><span><i class="fa-solid fa-user-tie me-1"></i><?php echo htmlspecialchars($course['teacher_name']); ?></span></div>
                                <a class="btn btn-outline-primary w-100" href="<?php echo SITE_URL; ?>/egitim.php?id=<?php echo (int)$course['id']; ?>">Eğitim Detayı</a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
