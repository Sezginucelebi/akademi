<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Telegram Bot Test Ekranı</h2>";
echo "<ul>";
echo "<li><strong>TELEGRAM_BOT_TOKEN:</strong> " . (defined('TELEGRAM_BOT_TOKEN') && !empty(TELEGRAM_BOT_TOKEN) ? 'Tanımlı (***' . substr(TELEGRAM_BOT_TOKEN, -4) . ')' : '<span style="color:red;">Tanımlı Değil veya Boş</span>') . "</li>";
echo "<li><strong>TELEGRAM_CHAT_ID:</strong> " . (defined('TELEGRAM_CHAT_ID') && !empty(TELEGRAM_CHAT_ID) ? 'Tanımlı (' . htmlspecialchars(TELEGRAM_CHAT_ID) . ')' : '<span style="color:red;">Tanımlı Değil veya Boş</span>') . "</li>";
echo "</ul>";

if (defined('TELEGRAM_BOT_TOKEN') && !empty(TELEGRAM_BOT_TOKEN) && defined('TELEGRAM_CHAT_ID') && !empty(TELEGRAM_CHAT_ID)) {
    $msg = "<b>🧪 Auronova Akademi Bot Testi</b>\n\nTelegram bot bildirim sistemi başarıyla çalışıyor! 🚀";

    $botToken = TELEGRAM_BOT_TOKEN;
    $chatId = TELEGRAM_CHAT_ID;
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $msg,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 5
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result !== false) {
        $resObj = json_decode($result, true);
        if (isset($resObj['ok']) && $resObj['ok'] === true) {
            echo "<div style='color: green; font-weight: bold; padding: 10px; background: #e8f5e9; border: 1px solid #c8e6c9;'>✅ Test mesajı Telegram'a başarıyla gönderildi! Lütfen Telegram kanalınızı / sohbetinizi kontrol edin.</div>";
        } else {
            echo "<div style='color: red; font-weight: bold; padding: 10px; background: #ffebee; border: 1px solid #ffcdd2;'>❌ Telegram API hata döndürdü: " . htmlspecialchars($result) . "</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold; padding: 10px; background: #ffebee; border: 1px solid #ffcdd2;'>❌ Telegram API'ye HTTP isteği gönderilemedi (cURL/file_get_contents veya ağ hatası).</div>";
    }
} else {
    echo "<div style='color: orange; font-weight: bold; padding: 10px; background: #fff3e0; border: 1px solid #ffe0b2;'>⚠️ Lütfen <code>config/config.php</code> dosyasındaki <code>TELEGRAM_BOT_TOKEN</code> ve <code>TELEGRAM_CHAT_ID</code> değerlerini doldurunuz.</div>";
}
