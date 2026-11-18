<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/functions.php';

$contactEmail = 'admin@auronovasoftware.com.tr';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Formun süresi dolmuş olabilir. Lütfen tekrar deneyin.');
    } else {
        $name = trim(strip_tags($_POST['name'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $subject = preg_replace('/[\r\n]+/', ' ', trim(strip_tags($_POST['subject'] ?? '')));
        $message = trim(strip_tags($_POST['message'] ?? ''));

        if ($name === '' || $subject === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('danger', 'Lütfen tüm alanları doldurun ve geçerli bir e-posta adresi girin.');
        } else {
            $mailSubject = 'Auronova Akademi iletişim formu: ' . $subject;
            $mailBody = "Ad Soyad: {$name}\nE-posta: {$email}\n\nMesaj:\n{$message}";
            $mailHeaders = "Content-Type: text/plain; charset=UTF-8\r\nReply-To: {$email}";

            if (mail($contactEmail, $mailSubject, $mailBody, $mailHeaders)) {
                setFlashMessage('success', 'Mesajınız gönderildi. Bizimle iletişime geçtiğiniz için teşekkür ederiz.');
            } else {
                setFlashMessage('danger', 'Mesaj şu anda gönderilemedi. Dilerseniz e-posta adresimiz üzerinden bize ulaşabilirsiniz.');
            }
        }
    }

    header('Location: ' . SITE_URL . '/iletisim.php');
    exit;
}

$pageTitle = 'İletişim — Auronova Akademi';
$csrfToken = generateCsrfToken();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="main-content">
    <section class="bg-gradient-primary text-white py-5">
        <div class="container py-3">
            <span class="text-uppercase small fw-semibold opacity-75">Auronova Akademi</span>
            <h1 class="display-5 fw-bold mt-2 mb-2">İletişim</h1>
            <p class="lead mb-0">Sorularınız ve geri bildirimleriniz için bize ulaşın.</p>
        </div>
    </section>
    <section class="container py-5">
        <div class="row g-4 justify-content-center align-items-stretch">
            <div class="col-lg-4">
                <div class="card card-custom h-100 border-0 p-4">
                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px"><i class="fa-solid fa-envelope fs-4"></i></div>
                    <h2 class="h5 fw-bold">İletişim Bilgileri</h2>
                    <div class="mt-2 mb-3">
                        <div class="d-flex gap-3 align-items-start mb-3"><i class="fa-solid fa-location-dot text-primary mt-1"></i><div><div class="small text-muted">Adres</div><div class="fw-medium">İstanbul, Türkiye</div></div></div>
                        <div class="d-flex gap-3 align-items-start"><i class="fa-solid fa-envelope text-primary mt-1"></i><div><div class="small text-muted">E-posta</div><a class="fw-semibold text-decoration-none" href="mailto:<?php echo htmlspecialchars($contactEmail); ?>"><?php echo htmlspecialchars($contactEmail); ?></a></div></div>
                        <div class="d-flex gap-3 align-items-start mt-3"><i class="fa-solid fa-phone text-primary mt-1"></i><div><div class="small text-muted">Telefon</div><a class="fw-semibold text-decoration-none" href="tel:+902121234567">0212 123-45-67</a></div></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card card-custom border-0 p-4 p-md-5">
                    <h2 class="h4 fw-bold mb-1">Bize mesaj gönderin</h2>
                    <p class="text-muted mb-4">Formu doldurun, size e-posta üzerinden yanıt verelim.</p>
                    <form action="<?php echo SITE_URL; ?>/iletisim.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="contact-name">Ad Soyad</label><input class="form-control" id="contact-name" name="name" type="text" maxlength="120" autocomplete="name" required></div>
                            <div class="col-md-6"><label class="form-label" for="contact-email">E-posta</label><input class="form-control" id="contact-email" name="email" type="email" maxlength="150" autocomplete="email" required></div>
                            <div class="col-12"><label class="form-label" for="contact-subject">Konu</label><input class="form-control" id="contact-subject" name="subject" type="text" maxlength="160" required></div>
                            <div class="col-12"><label class="form-label" for="contact-message">Mesajınız</label><textarea class="form-control" id="contact-message" name="message" rows="5" maxlength="5000" required></textarea></div>
                            <div class="col-12"><button class="btn btn-auronova px-4" type="submit"><i class="fa-solid fa-paper-plane me-2"></i>Mesajı Gönder</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
