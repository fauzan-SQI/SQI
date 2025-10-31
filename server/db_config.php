<?php
// Database configuration for Science-Qur'an Integration

// Include file konstanta global yang aman jika tersedia
$globalConstantsFile = __DIR__ . '/../config/global_constants.php';
if (file_exists($globalConstantsFile)) {
    require_once $globalConstantsFile;
}

// Include environment configuration hanya jika belum didefinisikan
// Cek apakah konstanta database sudah didefinisikan
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/environment.php';
} else {
    // Jika konstanta database sudah didefinisikan tetapi konstanta aplikasi belum, definisikan konstanta aplikasi
    // Gunakan konstanta global yang aman dengan pendekatan proteksi
    if (!defined('APP_NAME')) {
        define('APP_NAME', defined('GLOBAL_APP_NAME') ? GLOBAL_APP_NAME : 'Science-Qur\'an Integration');
        define('APP_VERSION', defined('GLOBAL_APP_VERSION') ? GLOBAL_APP_VERSION : '2.0');
    }
}

// Create connection using constants from environment
function getConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use real prepared statements
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            die("Connection failed: " . $e->getMessage());
        } else {
            die("Maaf, terjadi kesalahan pada sistem. Silakan coba lagi nanti.");
        }
    }
}

// Test connection
if (isset($_GET['test'])) {
    try {
        $conn = getConnection();
        echo "Connected successfully to database: " . DB_NAME;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

// Bersihkan variabel global yang tidak diperlukan lagi untuk mencegah konflik
unset($globalConstantsFile);
?>