<?php
$pageTitle = "Online Ders ve Canlı Eğitim Portalı";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

// Fetch published courses for landing showcase
$db = getDBConnection();
$stmt = $db->query("
    SELECT c.*, u.name as teacher_name,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons
    FROM courses c
    JOIN users u ON c.teacher_id = u.id
    WHERE c.status = 'published'
    ORDER BY c.created_at DESC LIMIT 6
");
$featuredCourses = $stmt->fetchAll();
?>

<?php require __DIR__ . '/includes/homepage_sections.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
