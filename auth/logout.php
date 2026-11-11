<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session for flash message
session_start();
setFlashMessage('info', 'Başarıyla çıkış yapıldı.');

header("Location: " . SITE_URL . "/auth/login.php");
exit;
