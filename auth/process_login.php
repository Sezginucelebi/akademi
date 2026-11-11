<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    setFlashMessage('danger', 'Geçersiz istek (CSRF doğrulama hatası). Lütfen tekrar deneyin.');
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    setFlashMessage('danger', 'Lütfen e-posta adresi ve parolanızı girin.');
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}

try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 1 LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    $portal = $_POST['portal'] ?? 'student';

    if ($user && password_verify($password, $user['password'])) {
        if ($portal === 'staff' && $user['role'] === 'student') {
            setFlashMessage('danger', 'Öğrenci hesapları Akademik Personel girişinden giriş yapamaz. Lütfen Öğrenci Girişi sekmesini kullanın.');
            header("Location: " . SITE_URL . "/auth/login.php");
            exit;
        }

        // Regenerate session id to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'];

        setFlashMessage('success', 'Hoş geldiniz, ' . htmlspecialchars($user['name']) . '!');

        if ($user['role'] === 'teacher') {
            header("Location: " . SITE_URL . "/teacher/dashboard.php");
        } elseif ($user['role'] === 'admin') {
            header("Location: " . SITE_URL . "/admin/dashboard.php");
        } else {
            header("Location: " . SITE_URL . "/student/dashboard.php");
        }
        exit;
    } else {
        setFlashMessage('danger', 'E-posta adresi veya parola hatalı.');
        header("Location: " . SITE_URL . "/auth/login.php");
        exit;
    }
} catch (Exception $e) {
    setFlashMessage('danger', 'Giriş sırasında bir hata oluştu: ' . $e->getMessage());
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}
