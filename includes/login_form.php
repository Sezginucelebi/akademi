<?php
// The calling page sets $loginRole to student or teacher.
$isStudentLogin = $loginRole === 'student';
$loginTitle = $isStudentLogin ? 'Öğrenci Girişi' : 'Öğretmen Girişi';
$csrfToken = generateCsrfToken();
?>
<main class="container py-5 main-content">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-custom border-0 shadow-sm p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="<?php echo SITE_URL; ?>/assets/img/icon.png" alt="Auronova Akademi" class="mb-3" style="width:64px;height:64px;object-fit:contain">
                    <h1 class="h3 fw-bold mb-2"><?php echo $loginTitle; ?></h1>
                    <p class="text-muted mb-0">Hesabınıza erişmek için bilgilerinizi girin.</p>
                </div>
                <form action="<?php echo SITE_URL; ?>/auth/process_login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="portal" value="<?php echo $loginRole; ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta adresi</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Parola</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <div class="text-end mb-4"><a class="small text-decoration-none" href="<?php echo SITE_URL; ?>/auth/forgot_password.php">Parolamı unuttum</a></div>
                    <button type="submit" class="btn btn-auronova btn-lg w-100">Giriş Yap</button>
                </form>
                <?php if ($isStudentLogin): ?>
                    <p class="small text-center text-muted mt-4 mb-0">Hesabınız yok mu? <a href="<?php echo SITE_URL; ?>/auth/login.php?open_register=1">Öğrenci olarak kayıt olun</a></p>
                <?php endif; ?>
                <div class="text-center mt-3"><a class="small text-decoration-none" href="<?php echo SITE_URL; ?>/auth/login.php">Giriş türünü değiştir</a></div>
            </div>
        </div>
    </div>
</main>
