<?php
// safe_include.php - File untuk meng-include komponen-komponen aplikasi dengan aman

// Membuat fungsi untuk include file konfigurasi secara aman
function safeIncludeConfig() {
    static $configIncluded = false;
    
    if ($configIncluded) {
        return;
    }
    
    // Include environment config hanya jika belum didefinisikan
    if (!defined('DB_HOST')) {
        require_once __DIR__ . '/config/environment.php';
    }
    
    // Include db_config hanya jika fungsi getConnection belum didefinisikan
    if (!function_exists('getConnection')) {
        require_once __DIR__ . '/server/db_config.php';
    }
    
    // Include config utama hanya jika konstanta utama belum didefinisikan
    if (!defined('APP_NAME')) {
        require_once __DIR__ . '/server/bootstrap.php';
    }
    
    $configIncluded = true;
}

// Fungsi untuk include class dengan aman
function safeIncludeClass($className, $filePath) {
    if (!class_exists($className, false)) {
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
}

// Fungsi untuk include fungsi dengan aman
function safeIncludeFunction($filePath) {
    static $includedFiles = [];
    
    if (!isset($includedFiles[$filePath])) {
        if (file_exists($filePath)) {
            require_once $filePath;
        }
        $includedFiles[$filePath] = true;
    }
}

// Gunakan fungsi-fungsi di atas untuk meng-include komponen dengan aman
safeIncludeConfig();
safeIncludeClass('AIHandler', __DIR__ . '/server/ai_handler.php');
safeIncludeClass('KeywordMatcher', __DIR__ . '/server/keyword_matcher.php');
safeIncludeClass('LogManager', __DIR__ . '/server/log_manager.php');
safeIncludeClass('TafsirService', __DIR__ . '/server/tafsir_service.php');
safeIncludeFunction(__DIR__ . '/server/video_config.php');
?>
