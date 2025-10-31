<?php
// server/db_config_fallback.php - Database configuration with fallback for Science-Qur'an Integration

// Include environment configuration
require_once __DIR__ . '/../config/environment.php';

// Database connection with error handling and fallback
if (!function_exists('getConnection')) {
    function getConnection() {
        global $pdo;
        
        // Jika koneksi PDO global belum dibuat
        if (!isset($pdo)) {
            try {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use real prepared statements
            } catch(PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                
                // Jika mode debug aktif, tampilkan error
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    throw new Exception("Connection failed: " . $e->getMessage());
                } else {
                    // Dalam mode production, tidak tampilkan error detail
                    throw new Exception("Database connection error");
                }
            }
        }
        
        return $pdo;
    }
}

// Fungsi untuk mengecek apakah database tersedia
if (!function_exists('isDatabaseAvailable')) {
    function isDatabaseAvailable() {
        try {
            $conn = getConnection();
            // Coba eksekusi query sederhana
            $stmt = $conn->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("Database check failed: " . $e->getMessage());
            return false;
        }
    }
}

// Fungsi untuk mendapatkan data fallback jika database tidak tersedia
if (!function_exists('getFallbackData')) {
    function getFallbackData($type = 'answer', $query = '') {
        $fallbackData = [
            'answer' => [
                'question_keywords' => $query,
                'answer_text' => 'Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan mencocokkan kata kunci dari pertanyaan Anda dengan database yang berisi penjelasan ilmiah dan ayat Al-Qur\'an terkait. Saat ini, server database tidak dapat diakses, mohon periksa koneksi dan pengaturan server Anda.',
                'quran_reference' => 'QS. Al-Baqarah: 2',
                'youtube_link' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'tafsir_data' => []
            ],
            'daily_fact' => [
                'fact_text' => 'Database tidak tersedia saat ini. Fakta sains harian akan muncul ketika server database dapat diakses.',
                'quran_reference' => 'QS. Al-Mulk: 3 - Dan Dialah yang menciptakan langit dan bumi dalam seisinya, dan Kami melangitkan langit itu dengan beberapa bintang, dan Kami menjaganya dari setiap syaitan yang berontak.'
            ]
        ];
        
        return $fallbackData[$type] ?? $fallbackData['answer'];
    }
}

// Test connection
if (isset($_GET['test'])) {
    try {
        $conn = getConnection();
        echo "Connected successfully to database: " . DB_NAME;
    } catch(Exception $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
?>