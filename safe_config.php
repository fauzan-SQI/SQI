<?php
// safe_config.php - Penggabungan konfigurasi yang aman untuk mencegah error pendefinisian ganda

// Include file konstanta global yang aman jika tersedia
// Prioritaskan file konstanta global yang baru dibuat
$globalConstantsFile = __DIR__ . '/config/global_constants.php';
if (file_exists($globalConstantsFile)) {
    require_once $globalConstantsFile;
} else {
    // Fallback ke pendekatan konvensional jika file global_constants.php tidak ada
    // Cek apakah konstanta aplikasi sudah didefinisikan
    $appConstantsDefined = defined('APP_NAME') && defined('APP_VERSION');

    // Cek apakah konstanta database sudah didefinisikan
    $dbConstantsDefined = defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS');

    // Include konfigurasi environment dan database hanya jika belum didefinisikan
    if (!$dbConstantsDefined || !$appConstantsDefined) {
        // Cek apakah environment.php ada dan belum di-include
        $environmentFile = __DIR__ . '/config/environment.php';
        if (file_exists($environmentFile)) {
            // Include environment config hanya jika konstanta belum didefinisikan
            if (!$dbConstantsDefined || !$appConstantsDefined) {
                require_once $environmentFile;
            }
        } else {
            // Jika file environment tidak ada, definisikan konstanta secara manual jika belum ada
            if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
            if (!defined('DB_NAME')) define('DB_NAME', 'science_quran');
            if (!defined('DB_USER')) define('DB_USER', 'root');
            if (!defined('DB_PASS')) define('DB_PASS', '');
            
            if (!defined('APP_NAME')) define('APP_NAME', 'Science-Qur\'an Integration');
            if (!defined('APP_VERSION')) define('APP_VERSION', '2.0');
        }
    }
}

// Cek apakah konstanta aplikasi sudah didefinisikan
$appConstantsDefined = defined('APP_NAME') && defined('APP_VERSION');

// Cek apakah konstanta database sudah didefinisikan
$dbConstantsDefined = defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS');

// Include konfigurasi environment dan database hanya jika belum didefinisikan
if (!$dbConstantsDefined || !$appConstantsDefined) {
    // Cek apakah environment.php ada dan belum di-include
    $environmentFile = __DIR__ . '/config/environment.php';
    if (file_exists($environmentFile)) {
        // Include environment config hanya jika konstanta belum didefinisikan
        if (!$dbConstantsDefined || !$appConstantsDefined) {
            require_once $environmentFile;
        }
    } else {
        // Jika file environment tidak ada, definisikan konstanta secara manual jika belum ada
        // Gunakan fungsi defineSafe jika tersedia, jika tidak gunakan pendekatan aman biasa
        if (function_exists('defineSafe')) {
            defineSafe('DB_HOST', 'localhost');
            defineSafe('DB_NAME', 'science_quran');
            defineSafe('DB_USER', 'root');
            defineSafe('DB_PASS', '');
            
            defineSafe('APP_NAME', 'Science-Qur\'an Integration');
            defineSafe('APP_VERSION', '2.0');
        } else {
            if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
            if (!defined('DB_NAME')) define('DB_NAME', 'science_quran');
            if (!defined('DB_USER')) define('DB_USER', 'root');
            if (!defined('DB_PASS')) define('DB_PASS', '');
            
            if (!defined('APP_NAME')) define('APP_NAME', 'Science-Qur\'an Integration');
            if (!defined('APP_VERSION')) define('APP_VERSION', '2.0');
        }
    }
}

// Include konfigurasi database utama dengan pendekatan aman
if (!function_exists('getConnection')) {
    require_once __DIR__ . '/server/db_config.php';
}

// Application settings - hanya define jika belum ada
// Gunakan fungsi defineSafe jika tersedia, jika tidak gunakan pendekatan aman biasa
if (function_exists('defineSafe')) {
    defineSafe('APP_NAME', 'Science-Qur\'an Integration');
    defineSafe('APP_VERSION', '2.0');
    defineSafe('API_TIMEOUT', 30); // seconds
    defineSafe('MAX_QUESTION_LENGTH', 500);
    defineSafe('MAX_RESPONSE_LENGTH', 5000);
    defineSafe('DEFAULT_ERROR_MESSAGE', 'Terjadi kesalahan saat memproses pertanyaan Anda. Silakan coba lagi.');
    defineSafe('DEFAULT_NO_RESULT_MESSAGE', 'Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan mencocokkan kata kunci dari pertanyaan Anda dengan database yang berisi penjelasan ilmiah dan ayat Al-Qur\'an terkait.');
} else {
    if (!defined('APP_NAME')) define('APP_NAME', 'Science-Qur\'an Integration');
    if (!defined('APP_VERSION')) define('APP_VERSION', '2.0');
    if (!defined('API_TIMEOUT')) define('API_TIMEOUT', 30); // seconds
    if (!defined('MAX_QUESTION_LENGTH')) define('MAX_QUESTION_LENGTH', 500);
    if (!defined('MAX_RESPONSE_LENGTH')) define('MAX_RESPONSE_LENGTH', 5000);
    if (!defined('DEFAULT_ERROR_MESSAGE')) define('DEFAULT_ERROR_MESSAGE', 'Terjadi kesalahan saat memproses pertanyaan Anda. Silakan coba lagi.');
    if (!defined('DEFAULT_NO_RESULT_MESSAGE')) define('DEFAULT_NO_RESULT_MESSAGE', 'Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan mencocokkan kata kunci dari pertanyaan Anda dengan database yang berisi penjelasan ilmiah dan ayat Al-Qur\'an terkait.');
}

// Common responses - hanya definisikan jika belum ada
if (!isset($commonResponses)) {
    $commonResponses = [
        'assalamualaikum' => 'Wa\'alaikumussalam warahmatullahi wabarakatuh. Silakan ajukan pertanyaan Anda seputar sains dan Al-Qur\'an.',
        'terima kasih' => 'Terima kasih telah menggunakan Science-Qur\'an Integration. Silakan ajukan pertanyaan Anda.',
        'siapa kamu' => 'Saya adalah asisten AI yang dirancang untuk membantu menjawab pertanyaan seputar sains dan Al-Qur\'an.',
        'bantu saya' => 'Tentu! Silakan ajukan pertanyaan Anda tentang hubungan antara sains dan Al-Qur\'an, dan saya akan bantu dengan penjelasan ilmiah serta referensi Al-Qur\'an yang relevan.'
    ];
}

// Include required classes - hanya jika belum diinclude
if (!class_exists('AIHandler')) {
    if (file_exists(__DIR__ . '/server/ai_handler.php')) {
        require_once __DIR__ . '/server/ai_handler.php';
    }
}
if (!class_exists('KeywordMatcher')) {
    if (file_exists(__DIR__ . '/server/keyword_matcher.php')) {
        require_once __DIR__ . '/server/keyword_matcher.php';
    }
}
if (!class_exists('LogManager')) {
    if (file_exists(__DIR__ . '/server/log_manager.php')) {
        require_once __DIR__ . '/server/log_manager.php';
    }
}
if (!class_exists('TafsirService')) {
    if (file_exists(__DIR__ . '/server/tafsir_service.php')) {
        require_once __DIR__ . '/server/tafsir_service.php';
    }
}

// Fungsi tambahan untuk aplikasi - hanya didefinisikan jika belum ada
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}

if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('validateUrl')) {
    function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        header("Location: $path");
        exit();
    }
}

if (!function_exists('logError')) {
    function logError($message, $context = '') {
        if (defined('LOG_ERRORS') && LOG_ERRORS) {
            $logMessage = date('Y-m-d H:i:s') . " - $message - Context: $context" . PHP_EOL;
            file_put_contents(LOG_FILE_PATH, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
}

if (!function_exists('logActivity')) {
    function logActivity($activity, $userId = null) {
        $logMessage = date('Y-m-d H:i:s') . " - Activity: $activity" . ($userId ? " - User ID: $userId" : "") . " - IP: " . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
        $logPath = defined('LOG_FILE_PATH') ? dirname(LOG_FILE_PATH) . '/activity.log' : __DIR__ . '/../logs/activity.log';
        file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

// Bersihkan variabel global yang tidak diperlukan lagi untuk mencegah konflik
unset($globalConstantsFile, $environmentFile, $dbConfigFile);
?>