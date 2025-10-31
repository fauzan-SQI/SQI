<?php
// auth/config.php - Konfigurasi untuk sistem otentikasi pengguna

// Pendekatan aman untuk menghindari konflik konstanta ganda
// Cek apakah konstanta aplikasi sudah didefinisikan
$appConstantsDefined = defined('APP_NAME') && defined('APP_VERSION');

// Cek apakah konstanta database sudah didefinisikan
$dbConstantsDefined = defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS');

// Include file konstanta global yang aman jika tersedia
$globalConstantsFile = __DIR__ . '/../config/global_constants.php';
if (file_exists($globalConstantsFile)) {
    require_once $globalConstantsFile;
}

// Include konfigurasi environment dan database hanya jika belum didefinisikan
if (!$dbConstantsDefined || !$appConstantsDefined) {
    // Cek apakah environment.php ada dan belum di-include
    $environmentFile = __DIR__ . '/../config/environment.php';
    if (file_exists($environmentFile)) {
        // Include environment config hanya jika konstanta belum didefinisikan
        if (!$dbConstantsDefined || !$appConstantsDefined) {
            require_once $environmentFile;
        }
    } else {
        // Jika file environment tidak ada, definisikan konstanta secara manual jika belum ada
        // Gunakan konstanta global yang aman jika tersedia
        if (!defined('DB_HOST')) define('DB_HOST', defined('GLOBAL_DB_HOST') ? GLOBAL_DB_HOST : 'localhost');
        if (!defined('DB_NAME')) define('DB_NAME', defined('GLOBAL_DB_NAME') ? GLOBAL_DB_NAME : 'science_quran');
        if (!defined('DB_USER')) define('DB_USER', defined('GLOBAL_DB_USER') ? GLOBAL_DB_USER : 'root');
        if (!defined('DB_PASS')) define('DB_PASS', defined('GLOBAL_DB_PASS') ? GLOBAL_DB_PASS : '');
        
        if (!defined('APP_NAME')) define('APP_NAME', defined('GLOBAL_APP_NAME') ? GLOBAL_APP_NAME : 'Science-Qur\'an Integration');
        if (!defined('APP_VERSION')) define('APP_VERSION', defined('GLOBAL_APP_VERSION') ? GLOBAL_APP_VERSION : '2.0');
    }
}

// Include konfigurasi database utama dengan pendekatan aman
if (!function_exists('getConnection')) {
    require_once __DIR__ . '/../server/db_config.php';
}

// Include fungsi-fungsi tambahan hanya jika belum didefinisikan
if (!function_exists('sanitizeInput')) {
    require_once __DIR__ . '/../server/config.php';
}

// Fungsi untuk hash password
if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
}

// Fungsi untuk verifikasi password
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

// Fungsi untuk mulai sesi pengguna
if (!function_exists('startUserSession')) {
    function startUserSession($userId, $username) {
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
    }
}

// Fungsi untuk cek apakah pengguna sudah login
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        // Cek apakah sesi sudah aktif sebelum memulai
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
}

// Fungsi untuk cek sesi aktif
if (!function_exists('isSessionValid')) {
    function isSessionValid() {
        // Cek apakah sesi sudah aktif sebelum memeriksa variabel sesi
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Sesi berlaku 30 menit
        if (time() - $_SESSION['login_time'] > (30 * 60)) {
            return false;
        }
        
        return true;
    }
}

// Fungsi untuk logout
if (!function_exists('logoutUser')) {
    function logoutUser() {
        // Cek apakah sesi sudah aktif sebelum memulai
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = array(); // Hapus semua variabel sesi
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy(); // Hancurkan sesi
    }
}

// Fungsi untuk mendapatkan ID pengguna saat ini
if (!function_exists('getCurrentUserId')) {
    function getCurrentUserId() {
        // Cek apakah sesi sudah aktif sebelum memeriksa variabel sesi
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
            return $_SESSION['user_id'];
        }
        return null;
    }
}

// Fungsi untuk log aktivitas login
if (!function_exists('logLoginActivity')) {
    function logLoginActivity($userId, $ipAddress, $userAgent) {
        try {
            // Cek apakah sesi sudah aktif untuk mendapatkan informasi tambahan
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $conn = getConnection();
            $stmt = $conn->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $ipAddress, $userAgent]);
        } catch (Exception $e) {
            error_log("Login activity logging failed: " . $e->getMessage());
        }
    }
}
?>