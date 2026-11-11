<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $googleId = sanitize($_POST['google_id'] ?? '');

    if (!empty($email) && !empty($name)) {
        try {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Automatically register new student with Google
                $ins = $db->prepare("INSERT INTO users (name, email, password, google_id, role, status) VALUES (:name, :email, NULL, :google_id, 'student', 1)");
                $ins->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':google_id' => $googleId
                ]);
                $userId = $db->lastInsertId();

                $user = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'student',
                    'avatar' => null
                ];
            } else {
                if (empty($user['google_id']) && !empty($googleId)) {
                    $upd = $db->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
                    $upd->execute([':google_id' => $googleId, ':id' => $user['id']]);
                }
            }

            if ($user && $user['status'] == 1) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_avatar'] = $user['avatar'] ?? null;

                $redirect = SITE_URL . '/student/dashboard.php';
                if ($user['role'] === 'teacher') {
                    $redirect = SITE_URL . '/teacher/dashboard.php';
                } elseif ($user['role'] === 'admin') {
                    $redirect = SITE_URL . '/admin/dashboard.php';
                }

                if (ob_get_length()) ob_clean();
                echo json_encode(['success' => true, 'redirect' => $redirect]);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
echo json_encode(['success' => false, 'message' => 'Geçersiz Google istek verisi.']);
exit;
