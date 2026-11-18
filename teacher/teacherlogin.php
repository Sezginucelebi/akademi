<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';

if (isLoggedIn()) {
    $user = currentUser();
    $destination = $user['role'] === 'teacher' ? 'teacher' : ($user['role'] === 'admin' ? 'admin' : 'student');
    header('Location: ' . SITE_URL . '/' . $destination . '/dashboard.php');
    exit;
}

$pageTitle = 'Öğretmen Girişi — Auronova Akademi';
$loginRole = 'teacher';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/login_form.php';
require_once __DIR__ . '/../includes/footer.php';
