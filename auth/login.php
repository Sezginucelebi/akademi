<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';

// If already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    $user = currentUser();
    if ($user['role'] === 'teacher') {
        header("Location: " . SITE_URL . "/teacher/dashboard.php");
    } elseif ($user['role'] === 'admin') {
        header("Location: " . SITE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . SITE_URL . "/student/dashboard.php");
    }
    exit;
}

$pageTitle = "Giriş Yap — Auronova Akademi";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$csrfToken = generateCsrfToken();
$activeTab = $_GET['tab'] ?? 'student';
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-custom p-4 p-md-4 shadow-lg border-0">
                <div class="text-center mb-3">
                    <img src="<?php echo SITE_URL; ?>/assets/img/icon.png" alt="Auronova Akademi Icon" class="mb-2 shadow-sm rounded-circle" style="width: 64px; height: 64px; object-fit: contain;">
                    <h3 class="fw-bold text-dark mb-1">Auronova Akademi</h3>
                    <p class="text-muted small">Lütfen giriş yapmak istediğiniz portalı seçin</p>
                </div>

                <!-- Portal Selection Tabs (Equal & Balanced Styling) -->
                <ul class="nav nav-pills nav-justified mb-4 bg-light p-1 rounded-3" id="loginPortalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2 px-3 rounded-2 border-0 <?php echo $activeTab !== 'staff' ? 'bg-white text-primary shadow-sm' : 'text-secondary bg-transparent'; ?>" id="student-tab" data-bs-toggle="pill" data-bs-target="#student-login" type="button" role="tab" aria-controls="student-login" aria-selected="<?php echo $activeTab !== 'staff' ? 'true' : 'false'; ?>" style="transition: all 0.2s;">
                            <i class="fa-solid fa-user-graduate me-1"></i> Öğrenci Girişi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2 px-3 rounded-2 border-0 <?php echo $activeTab === 'staff' ? 'bg-white text-primary shadow-sm' : 'text-secondary bg-transparent'; ?>" id="staff-tab" data-bs-toggle="pill" data-bs-target="#staff-login" type="button" role="tab" aria-controls="staff-login" aria-selected="<?php echo $activeTab === 'staff' ? 'true' : 'false'; ?>" style="transition: all 0.2s;">
                            <i class="fa-solid fa-chalkboard-user me-1"></i> Akademik Personel
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="loginPortalContent">
                    <!-- Student Login Tab -->
                    <div class="tab-pane fade <?php echo $activeTab !== 'staff' ? 'show active' : ''; ?>" id="student-login" role="tabpanel" aria-labelledby="student-tab">
                        <form action="<?php echo SITE_URL; ?>/auth/process_login.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="portal" value="student">

                            <div class="mb-3">
                                <label for="student_email" class="form-label font-weight-medium small">Öğrenci E-Posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="student_email" name="email" required placeholder="ogrenci@domain.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="student_password" class="form-label font-weight-medium small">Parola</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="student_password" name="password" required placeholder="••••••••">
                                    <button class="btn btn-light border border-start-0 text-muted px-3" type="button" onclick="togglePassword('student_password', 'toggleStudentIcon')" title="Şifreyi Göster/Gizle">
                                        <i class="fa-solid fa-eye" id="toggleStudentIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-end align-items-center">
                                <a href="<?php echo SITE_URL; ?>/auth/forgot_password.php" class="small text-decoration-none fw-medium text-primary">Şifremi Unuttum?</a>
                            </div>

                            <button type="submit" class="btn btn-auronova w-100 py-2 fs-6 mb-3 shadow-sm">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>Öğrenci Girişi Yap
                            </button>
                        </form>

                        <div class="text-center mt-3 pt-3 border-top">
                            <p class="small text-muted mb-2">Öğrenci hesabınız yok mu?</p>
                            <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-outline-primary w-100 py-2 fs-6">
                                <i class="fa-solid fa-user-plus me-2"></i>Öğrenci Olarak Kayıt Ol
                            </a>
                        </div>
                    </div>

                    <!-- Staff Login Tab -->
                    <div class="tab-pane fade <?php echo $activeTab === 'staff' ? 'show active' : ''; ?>" id="staff-login" role="tabpanel" aria-labelledby="staff-tab">
                        <div class="alert alert-warning border-0 small py-2 px-3 mb-3">
                            <i class="fa-solid fa-shield-halved me-1"></i> Bu alan yalnızca <strong>Eğitmenler ve Yöneticiler</strong> içindir.
                        </div>

                        <form action="<?php echo SITE_URL; ?>/auth/process_login.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="portal" value="staff">

                            <div class="mb-3">
                                <label for="staff_email" class="form-label font-weight-medium small">Personel E-Posta Adresi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="staff_email" name="email" required placeholder="egitmen@auronovasoftware.com.tr">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="staff_password" class="form-label font-weight-medium small">Parola</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="staff_password" name="password" required placeholder="••••••••">
                                    <button class="btn btn-light border border-start-0 text-muted px-3" type="button" onclick="togglePassword('staff_password', 'toggleStaffIcon')" title="Şifreyi Göster/Gizle">
                                        <i class="fa-solid fa-eye" id="toggleStaffIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 d-flex justify-content-end align-items-center">
                                <a href="<?php echo SITE_URL; ?>/auth/forgot_password.php" class="small text-decoration-none fw-medium text-primary">Şifremi Unuttum?</a>
                            </div>

                            <button type="submit" class="btn btn-auronova w-100 py-2 fs-6 mb-3 shadow-sm">
                                <i class="fa-solid fa-user-shield me-2"></i>Akademik Personel Girişi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Ensure Bootstrap tab switching updates styling dynamically
document.querySelectorAll('#loginPortalTabs button[data-bs-toggle="pill"]').forEach(button => {
    button.addEventListener('shown.bs.tab', event => {
        document.querySelectorAll('#loginPortalTabs button').forEach(btn => {
            btn.classList.remove('bg-white', 'text-primary', 'shadow-sm');
            btn.classList.add('text-secondary', 'bg-transparent');
        });
        event.target.classList.remove('text-secondary', 'bg-transparent');
        event.target.classList.add('bg-white', 'text-primary', 'shadow-sm');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
