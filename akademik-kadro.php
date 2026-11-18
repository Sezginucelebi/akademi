<?php
$pageTitle = 'Akademik Kadro — Auronova Akademi';
require_once __DIR__ . '/config/database.php';
$db = getDBConnection();
$teachers = $db->query("SELECT name, avatar, bio, expertise FROM users WHERE role = 'teacher' AND status = 1 AND bio IS NOT NULL AND CHAR_LENGTH(TRIM(bio)) >= 50 ORDER BY name ASC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="main-content">
    <section class="bg-gradient-primary text-white py-5">
        <div class="container py-3">
            <span class="text-uppercase small fw-semibold opacity-75">Auronova Akademi</span>
            <h1 class="display-5 fw-bold mt-2 mb-2">Akademik Kadro</h1>
            <p class="lead mb-0">Eğitim içeriklerini hazırlayan eğitmenlerimizle tanışın.</p>
        </div>
    </section>
    <section class="container py-5">
        <?php if (empty($teachers)): ?>
            <div class="alert alert-light border text-center p-4">Eğitmenler öz geçmişlerini ekledikçe akademik kadro burada listelenecek.</div>
        <?php else: ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($teachers as $teacher): ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <article class="card card-custom h-100 border-0 text-center p-4">
                            <?php if (!empty($teacher['avatar'])): ?>
                                <img src="<?php echo SITE_URL . '/' . htmlspecialchars($teacher['avatar']); ?>" alt="<?php echo htmlspecialchars($teacher['name']); ?>" class="rounded-circle mx-auto mb-3" style="width:104px;height:104px;object-fit:cover">
                            <?php else: ?>
                                <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width:104px;height:104px"><i class="fa-solid fa-chalkboard-user fs-1"></i></div>
                            <?php endif; ?>
                            <h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($teacher['name']); ?></h2>
                            <p class="small text-muted mb-0">Eğitmen</p>
                            <?php $expertiseAreas = array_filter(array_map('trim', explode(',', $teacher['expertise'] ?? ''))); ?>
                            <?php if ($expertiseAreas): ?><div class="d-flex flex-wrap justify-content-center gap-1 mt-3"><?php foreach ($expertiseAreas as $area): ?><span class="badge rounded-pill bg-light text-primary border"><?php echo htmlspecialchars($area); ?></span><?php endforeach; ?></div><?php endif; ?>
                            <p class="small text-muted mt-3 mb-0 text-start"><?php echo nl2br(htmlspecialchars($teacher['bio'])); ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
