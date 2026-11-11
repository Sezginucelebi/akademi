<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #2b2d42;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand-custom {
            font-weight: 700;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .btn-auronova {
            background-color: #1e3c72;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-auronova:hover {
            background-color: #2a5298;
            color: #ffffff;
        }
        .main-content {
            flex: 1;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #ffffff;
            border-right: 1px solid #e9ecef;
        }
        .sidebar .nav-link {
            color: #495057;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .sidebar .nav-link.active {
            background-color: #eef2ff;
            color: #1e3c72;
        }
        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
