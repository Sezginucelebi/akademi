<?php
$pageTitle = 'Hakkımızda — Auronova Akademi';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="main-content">
    <section class="bg-gradient-primary text-white py-5">
        <div class="container py-4">
            <span class="text-uppercase small fw-semibold opacity-75">Auronova Akademi</span>
            <h1 class="display-5 fw-bold mt-2 mb-3">Öğrenme yolculuğunuz burada başlar</h1>
            <p class="lead mb-0" style="max-width: 720px">Teknoloji alanındaki bilgi ve becerilerinizi çevrimiçi dersler, eğitim materyalleri ve canlı öğrenme deneyimleriyle geliştirin.</p>
        </div>
    </section>
    <section class="container py-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <h2 class="h3 fw-bold mb-3">Akademi hakkında</h2>
                <p class="text-muted">Auronova Akademi; yazılım, veri bilimi ve tasarım gibi teknoloji alanlarında öğrenmeyi kolaylaştırmak için hazırlanmış çevrimiçi bir eğitim platformudur. Öğrenciler kurs içeriklerine kendi hızlarında erişebilir, ders materyallerini takip edebilir ve uygun olduğunda canlı derslere katılabilir.</p>
                <p class="text-muted mb-0">Eğitmenler derslerini platform üzerinden paylaşır ve öğrencilerin öğrenme sürecini destekler. Amacımız, öğrenme kaynaklarını düzenli ve erişilebilir bir deneyim içinde sunmaktır.</p>
            </div>
            <div class="col-lg-5">
                <div class="card card-custom border-0 p-4 p-md-5 bg-light">
                    <div class="d-flex gap-3 mb-4"><i class="fa-solid fa-laptop-code text-primary fs-3"></i><div><h3 class="h6 fw-bold">Çevrimiçi öğrenme</h3><p class="small text-muted mb-0">Ders içeriklerine dilediğiniz zaman erişin.</p></div></div>
                    <div class="d-flex gap-3 mb-4"><i class="fa-solid fa-chalkboard-user text-primary fs-3"></i><div><h3 class="h6 fw-bold">Eğitmen desteği</h3><p class="small text-muted mb-0">Alanında uzman eğitmenlerin hazırladığı kursları keşfedin.</p></div></div>
                    <div class="d-flex gap-3"><i class="fa-solid fa-chart-line text-primary fs-3"></i><div><h3 class="h6 fw-bold">İlerleme takibi</h3><p class="small text-muted mb-0">Eğitim sürecinizi öğrenci panelinizden takip edin.</p></div></div>
                </div>
            </div>
        </div>
        <div class="text-center mt-5"><a href="<?php echo SITE_URL; ?>/akademik-kadro.php" class="btn btn-auronova px-4">Akademik Kadroyu Tanıyın</a></div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
