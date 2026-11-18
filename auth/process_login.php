<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

function loginPageForPortal($portal, $returnTo = '') {
    $url = SITE_URL . '/auth/login.php?portal=' . ($portal === 'teacher' ? 'teacher' : 'student');
    if (is_string($returnTo) && preg_match('#^/(?!/)[A-Za-z0-9/_?&=.%+-]*$#', $returnTo) && strpos($returnTo, '..') === false) {
        $url .= '&return_to=' . rawurlencode($returnTo);
    }
    return $url;
}

$portal = $_POST['portal'] ?? 'student';
$returnTo = $_POST['return_to'] ?? '';
$loginPage = loginPageForPortal($portal, $returnTo);
$isSafeReturnTo = is_string($returnTo)
    && preg_match('#^/(?!/)[A-Za-z0-9/_?&=.%+-]*$#', $returnTo)
    && strpos($returnTo, '..') === false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . SITE_URL . "/auth/login.php");
    exit;
}

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    setFlashMessage('danger', 'Geçersiz istek (CSRF doğrulama hatası). Lütfen tekrar deneyin.');
    header("Location: " . $loginPage);
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    setFlashMessage('danger', 'Lütfen e-posta adresi ve parolanızı girin.');
    header("Location: " . $loginPage);
    exit;
}

try {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 1 LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $portalRoleMatches = ($portal === 'student' && $user['role'] === 'student')
            || ($portal === 'teacher' && in_array($user['role'], ['teacher', 'admin'], true));
        if (!$portalRoleMatches) {
            setFlashMessage('danger', 'Bu hesap seçilen giriş türüyle kullanılamaz. Lütfen doğru giriş ekranını seçin.');
            header("Location: " . $loginPage);
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

        if ($isSafeReturnTo) {
            header('Location: ' . SITE_URL . $returnTo);
            exit;
        }

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
        header("Location: " . $loginPage);
        exit;
    }
} catch (Exception $e) {
    setFlashMessage('danger', 'Giriş sırasında bir hata oluştu: ' . $e->getMessage());
    header("Location: " . $loginPage);
    exit;
}
