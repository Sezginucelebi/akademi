<?php

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return sprintf(
            '<div class="alert alert-%s alert-dismissible fade show my-3" role="alert">
                %s
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>',
            htmlspecialchars($flash['type']),
            htmlspecialchars($flash['message'])
        );
    }
    return '';
}

function formatDate($dateString) {
    if (!$dateString) return '-';
    $timestamp = strtotime($dateString);
    return date('d.m.Y H:i', $timestamp);
}

function getYouTubeEmbedUrl($url) {
    if (empty($url)) return '';

    // Check if already embed URL
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }

    // Convert watch URL (v=ID) to embed URL
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    if (isset($matches[1])) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    return $url;
}
