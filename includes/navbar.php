<?php
$user = currentUser();
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
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-auronova px-4"><i class="fa-solid fa-right-to-bracket me-2"></i>Giriş Yap</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-2">
    <?php echo getFlashMessage(); ?>
</div>
