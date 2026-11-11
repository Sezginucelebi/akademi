<?php
require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'student',
        'avatar' => $_SESSION['user_avatar'] ?? null,
    ];
}

function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/auth/login.php");
        exit;
    }
}

function requireRole($roles) {
    requireAuth();
    $user = currentUser();
    $allowedRoles = is_array($roles) ? $roles : [$roles];

    if (!in_array($user['role'], $allowedRoles)) {
        switch ($user['role']) {
            case 'teacher':
                header("Location: " . SITE_URL . "/teacher/dashboard.php");
                break;
            case 'admin':
                header("Location: " . SITE_URL . "/admin/dashboard.php");
                break;
            default:
                header("Location: " . SITE_URL . "/student/dashboard.php");
                break;
        }
        exit;
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
