<?php
require_once __DIR__ . '/config/database.php';

$db = getDBConnection();
$newHash = password_hash('password123', PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE users SET password = :pass");
$stmt->execute([':pass' => $newHash]);

echo "All passwords updated to password123 successfully!\n";

$check = $db->query("SELECT email, password FROM users")->fetchAll();
foreach ($check as $u) {
    $ok = password_verify('password123', $u['password']) ? 'OK' : 'FAIL';
    echo $u['email'] . " => " . $ok . "\n";
}
