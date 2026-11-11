<?php
require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Host ve Port ayrıştırma (Örn: "localhost:3306" veya "localhost")
        $dbHost = DB_HOST;
        $dbPort = null;
        if (strpos($dbHost, ':') !== false) {
            $parts = explode(':', $dbHost, 2);
            $dbHost = $parts[0];
            $dbPort = $parts[1];
        }

        $portParam = $dbPort ? ";port=" . $dbPort : "";

        $lastException = null;

        // 1. Doğrudan hedeflenen veritabanına bağlanmayı dene
        try {
            $dsn = sprintf("mysql:host=%s%s;dbname=%s;charset=%s", $dbHost, $portParam, DB_NAME, DB_CHARSET);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $lastException = $e;

            // 2. Eğer veritabanı henüz mevcut değilse (Örn: Yerel XAMPP ortamında ilk kurulum)
            $isDbNotFound = ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false);
            if ($isDbNotFound) {
                try {
                    $dsnNoDb = sprintf("mysql:host=%s%s;charset=%s", $dbHost, $portParam, DB_CHARSET);
                    $pdoTemp = new PDO($dsnNoDb, DB_USER, DB_PASS, $options);
                    $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

                    $dsn = sprintf("mysql:host=%s%s;dbname=%s;charset=%s", $dbHost, $portParam, DB_NAME, DB_CHARSET);
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                } catch (PDOException $exTemp) {
                    $lastException = $exTemp;
                }
            }

            // 3. Yerel geliştirme ortamında kullanıcı adı uyuşmazlığı varsa (root / şifresiz denemesi)
            if ($pdo === null && DB_USER !== 'root') {
                try {
                    $dsnRoot = sprintf("mysql:host=%s%s;dbname=%s;charset=%s", $dbHost, $portParam, DB_NAME, DB_CHARSET);
                    $pdo = new PDO($dsnRoot, 'root', '', $options);
                } catch (PDOException $exRoot) {
                    try {
                        $dsnNoDbRoot = sprintf("mysql:host=%s%s;charset=%s", $dbHost, $portParam, DB_CHARSET);
                        $pdoTempRoot = new PDO($dsnNoDbRoot, 'root', '', $options);
                        $pdoTempRoot->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

                        $dsnRootCreated = sprintf("mysql:host=%s%s;dbname=%s;charset=%s", $dbHost, $portParam, DB_NAME, DB_CHARSET);
                        $pdo = new PDO($dsnRootCreated, 'root', '', $options);
                    } catch (PDOException $exRootCreated) {
                        // Son hata saklanır
                    }
                }
            }
        }

        // Bağlantı tam olarak kurulamadıysa detaylı açıklama ekranı göster
        if ($pdo === null) {
            $errorMsg = $lastException ? $lastException->getMessage() : 'Bilinmeyen veritabanı hatası.';

            echo "<div style='font-family: Arial, sans-serif; max-width: 650px; margin: 50px auto; padding: 25px; background: #fff3f3; color: #a94442; border: 1px solid #ebccd1; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>";
            echo "<h3 style='margin-top: 0; color: #a94442;'><i class='fa-solid fa-triangle-exclamation'></i> Veritabanı Bağlantı Hatası</h3>";
            echo "<p>MySQL / MariaDB servisine veya hedeflenen veritabanına bağlanılamadı.</p>";
            echo "<div style='background: #fff; padding: 12px; border-radius: 6px; border: 1px solid #ebccd1; font-size: 13px; margin-bottom: 15px;'>";
            echo "<strong>Bağlantı Bilgileri:</strong><br>";
            echo "• Host: <code>" . htmlspecialchars(DB_HOST) . "</code><br>";
            echo "• Veritabanı: <code>" . htmlspecialchars(DB_NAME) . "</code><br>";
            echo "• Kullanıcı: <code>" . htmlspecialchars(DB_USER) . "</code>";
            echo "</div>";
            echo "<p style='font-size: 13px; color: #555; background: #f9f9f9; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;'><strong>Hata Detayı:</strong> " . htmlspecialchars($errorMsg) . "</p>";
            echo "<div style='font-size: 13px; color: #333; margin-top: 15px;'>";
            echo "<strong>Çözüm Önerileri:</strong>";
            echo "<ol style='padding-left: 20px; margin-top: 5px;'>";
            echo "<li><strong>Port Kontrolü:</strong> Varsayılan MySQL portu <code>3306</code>'dır. Port kısmına <code>3360</code> yazdıysanız bu bir yazım hatasıdır, lütfen <code>3306</code> veya <code>localhost</code> olarak düzeltin.</li>";
            echo "<li><strong>Yerel Ortamda (XAMPP):</strong> XAMPP Control Panel'i açarak <u>MySQL servisinin başladığından (Start)</u> emin olun.</li>";
            echo "<li><strong>Canlı Sunucuda (Plesk):</strong> Plesk panelden veritabanı adı, kullanıcı adı ve şifresinin doğru olduğunu kontrol edin. Sunucu içi MySQL bağlantıları için DB Host genellikle <code>localhost</code> olur.</li>";
            echo "</ol>";
            echo "</div>";
            echo "</div>";
            exit;
        }

        // Auto-seed schema if tables don't exist yet
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll();
            if (empty($tables)) {
                $schemaFile = __DIR__ . '/../database/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $stmtSql) {
                        if (!empty($stmtSql) && stripos($stmtSql, 'CREATE DATABASE') === false && stripos($stmtSql, 'USE ') === false) {
                            try {
                                $pdo->exec($stmtSql);
                            } catch (Exception $ex) {
                                // Tablo oluşturma uyarılarını yut
                            }
                        }
                    }
                }
            }

            // Ensure phone column exists on users table
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(50) DEFAULT NULL");
            } catch (Exception $e) {
                // Ignore if column already exists or not supported
            }

            // Ensure demo accounts exist (prevents lockout on clean database)
            $userCountStmt = $pdo->query("SELECT COUNT(*) FROM users");
            $userCount = $userCountStmt ? $userCountStmt->fetchColumn() : 0;
            if ($userCount < 3) {
                $defaultPass = password_hash('password123', PASSWORD_DEFAULT);

                $chkAdmin = $pdo->query("SELECT id FROM users WHERE email = 'admin@auronovasoftware.com.tr'")->fetch();
                if (!$chkAdmin) {
                    $pdo->exec("INSERT INTO users (name, email, password, role, status) VALUES ('Sistem Yöneticisi', 'admin@auronovasoftware.com.tr', '$defaultPass', 'admin', 1)");
                }

                $chkTeacher = $pdo->query("SELECT id FROM users WHERE email = 'ogretmen@auronovasoftware.com.tr'")->fetch();
                if (!$chkTeacher) {
                    $pdo->exec("INSERT INTO users (name, email, password, role, status) VALUES ('Ahmet Yılmaz (Eğitmen)', 'ogretmen@auronovasoftware.com.tr', '$defaultPass', 'teacher', 1)");
                }

                $chkStudent = $pdo->query("SELECT id FROM users WHERE email = 'ogrenci@auronovasoftware.com.tr'")->fetch();
                if (!$chkStudent) {
                    $pdo->exec("INSERT INTO users (name, email, password, role, status) VALUES ('Mehmet Demir (Öğrenci)', 'ogrenci@auronovasoftware.com.tr', '$defaultPass', 'student', 1)");
                }
            }
        } catch (Exception $schemaEx) {
            // Şema kontolü hatasını yut
        }
    }

    return $pdo;
}
