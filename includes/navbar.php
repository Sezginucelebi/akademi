<?php
$user = currentUser();
$flashMessage = getFlashMessage();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center py-1" href="<?php echo SITE_URL; ?>">
            <img src="<?php echo SITE_URL; ?>/assets/img/icon.png" alt="Logo" style="height: 64px; width: auto; object-fit: contain;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Menüyü Geç">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/index.php">Ana Sayfa</a>
                </li>
                <li class="nav-item"><a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/egitimler.php">Eğitimler</a></li>
                <li class="nav-item"><a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/hakkimizda.php">Hakkımızda</a></li>
                <li class="nav-item"><a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/akademik-kadro.php">Akademik Kadro</a></li>
                <li class="nav-item"><a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/iletisim.php">İletişim</a></li>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/student/dashboard.php">Derslerim</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/student/courses.php">Ders Kataloğu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/student/profile.php">Profilim</a>
                        </li>
                    <?php elseif ($user['role'] === 'teacher'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/teacher/dashboard.php">Öğretmen Paneli</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/teacher/create_course.php">Yeni Ders Oluştur</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/teacher/profile.php">Öz Geçmişim</a>
                        </li>
                    <?php elseif ($user['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/admin/dashboard.php">Yönetim Paneli</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if ($user): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px; font-weight: 600;">
                                <?php echo mb_substr($user['name'], 0, 1, 'UTF-8'); ?>
                            </div>
                            <span class="fw-medium small"><?php echo htmlspecialchars($user['name']); ?></span>
                            <span class="badge bg-secondary ms-1 small text-capitalize"><?php echo $user['role'] === 'student' ? 'Öğrenci' : ($user['role'] === 'teacher' ? 'Öğretmen' : 'Admin'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
                            <li><h6 class="dropdown-header"><?php echo htmlspecialchars($user['email']); ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($user['role'] === 'student'): ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/student/dashboard.php"><i class="fa-solid fa-gauge me-2 text-primary"></i>Panelim</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/student/profile.php"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Profil Ayarları</a></li>
                            <?php elseif ($user['role'] === 'teacher'): ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/teacher/dashboard.php"><i class="fa-solid fa-gauge me-2 text-primary"></i>Öğretmen Paneli</a></li>
                            <?php elseif ($user['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard.php"><i class="fa-solid fa-user-shield me-2 text-primary"></i>Admin Paneli</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Çıkış Yap</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn btn-auronova px-4" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fa-solid fa-right-to-bracket me-2"></i>Giriş Yap</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if (!$user): ?>
<style>
#loginModal #loginTabContent .tab-pane { display: none; }
#loginModal #loginTabContent .tab-pane.active { display: block; opacity: 1; }
</style>
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h2 class="modal-title fs-5 fw-bold" id="loginModalTitle">Giriş Yap</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-4">
                <?php if (isset($_GET['portal']) || (($_GET['open_register'] ?? '') === '1')) echo $flashMessage; ?>
                <ul class="nav nav-pills nav-fill mb-4" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" id="modal-student-tab" data-login-pane="modal-student-login" type="button" role="tab" aria-selected="true"><i class="fa-solid fa-user-graduate me-1"></i>Öğrenci</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="modal-teacher-tab" data-login-pane="modal-teacher-login" type="button" role="tab" aria-selected="false"><i class="fa-solid fa-chalkboard-user me-1"></i>Öğretmen</button></li>
                </ul>
                <div class="tab-content" id="loginTabContent">
                    <?php foreach (['student' => 'modal-student-login', 'teacher' => 'modal-teacher-login'] as $portal => $paneId): ?>
                    <div class="tab-pane <?php echo $portal === 'student' ? 'active' : ''; ?>" id="<?php echo $paneId; ?>" role="tabpanel">
                        <form action="<?php echo SITE_URL; ?>/auth/process_login.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                            <input type="hidden" name="portal" value="<?php echo $portal; ?>">
                            <?php if (isset($_GET['return_to']) && is_string($_GET['return_to'])): ?><input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_GET['return_to']); ?>"><?php endif; ?>
                            <div class="mb-3"><label class="form-label" for="modal-<?php echo $portal; ?>-email">E-posta adresi</label><input class="form-control" type="email" id="modal-<?php echo $portal; ?>-email" name="email" autocomplete="username" required></div>
                            <div class="mb-3"><label class="form-label" for="modal-<?php echo $portal; ?>-password">Parola</label><input class="form-control" type="password" id="modal-<?php echo $portal; ?>-password" name="password" autocomplete="current-password" required></div>
                            <div class="text-end mb-3"><a class="small text-decoration-none" href="<?php echo SITE_URL; ?>/auth/forgot_password.php">Parolamı unuttum</a></div>
                            <button class="btn btn-auronova w-100 py-2" type="submit">Giriş Yap</button>
                        </form>
                        <?php if ($portal === 'student'): ?><p class="small text-center text-muted mt-3 mb-0">Hesabınız yok mu? <a href="#" data-show-register>Kayıt olun</a></p><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                    <div id="modal-register" class="d-none">
                        <button type="button" class="btn btn-link btn-sm text-decoration-none px-0 mb-3" data-show-login><i class="fa-solid fa-arrow-left me-1"></i>Girişe dön</button>
                        <h3 class="h5 fw-bold mb-3">Öğrenci hesabı oluştur</h3>
                        <form action="<?php echo SITE_URL; ?>/auth/register.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                            <input type="hidden" name="modal" value="1">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6"><label class="form-label" for="register-first-name">Ad</label><input class="form-control" type="text" id="register-first-name" name="first_name" autocomplete="given-name" required></div>
                                <div class="col-sm-6"><label class="form-label" for="register-last-name">Soyad</label><input class="form-control" type="text" id="register-last-name" name="last_name" autocomplete="family-name" required></div>
                            </div>
                            <div class="mb-3"><label class="form-label" for="register-email">E-posta adresi</label><input class="form-control" type="email" id="register-email" name="email" autocomplete="email" required></div>
                            <div class="mb-3"><label class="form-label" for="register-phone">Telefon <span class="text-muted small">(isteğe bağlı)</span></label><input class="form-control" type="tel" id="register-phone" name="phone" autocomplete="tel"></div>
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6"><label class="form-label" for="register-password">Parola</label><input class="form-control" type="password" id="register-password" name="password" minlength="6" autocomplete="new-password" required></div>
                                <div class="col-sm-6"><label class="form-label" for="register-confirm-password">Parola tekrar</label><input class="form-control" type="password" id="register-confirm-password" name="confirm_password" minlength="6" autocomplete="new-password" required></div>
                            </div>
                            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="register-kvkk" name="kvkk" required><label class="form-check-label small" for="register-kvkk">Kişisel verilerimin <a href="<?php echo SITE_URL; ?>/auth/register.php#kvkkModal" target="_blank">KVKK Aydınlatma Metni</a> kapsamında işlenmesini onaylıyorum.</label></div>
                            <button class="btn btn-auronova w-100 py-2" type="submit"><i class="fa-solid fa-user-plus me-2"></i>Öğrenci hesabı oluştur</button>
                        </form>
                    </div>
            </div>
        </div>
    </div>
</div>
<script>
function selectLoginPane(paneId) {
    document.querySelectorAll('#loginModal [data-login-pane]').forEach(button => {
        const selected = button.dataset.loginPane === paneId;
        button.classList.toggle('active', selected);
        button.setAttribute('aria-selected', selected ? 'true' : 'false');
    });
    document.querySelectorAll('#loginModal #loginTabContent .tab-pane').forEach(pane => {
        pane.classList.toggle('active', pane.id === paneId);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const portal = params.get('portal');
    const openRegister = params.get('open_register') === '1';
    document.querySelectorAll('#loginModal [data-login-pane]').forEach(button => button.addEventListener('click', () => selectLoginPane(button.dataset.loginPane)));
    if (!openRegister && !['student', 'teacher'].includes(portal)) return;
    if (!openRegister) {
        selectLoginPane(portal === 'teacher' ? 'modal-teacher-login' : 'modal-student-login');
    }
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal'));
    modal.show();
    if (openRegister) showRegistrationForm(true);
});

function showRegistrationForm(show) {
    const tabs = document.querySelector('#loginModal .nav[role="tablist"]');
    const content = document.querySelector('#loginModal .tab-content');
    const registration = document.getElementById('modal-register');
    tabs.classList.toggle('d-none', show);
    content.classList.toggle('d-none', show);
    registration.classList.toggle('d-none', !show);
}

document.querySelectorAll('[data-show-register]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    showRegistrationForm(true);
}));
document.querySelectorAll('[data-show-login]').forEach(button => button.addEventListener('click', () => showRegistrationForm(false)));
</script>
<?php endif; ?>

<div class="container mt-2">
    <?php if (!isset($_GET['portal']) && (($_GET['open_register'] ?? '') !== '1')) echo $flashMessage; ?>
</div>
