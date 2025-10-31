<?php
// server/log_manager.php - Pengelolaan log untuk Science-Qur'an Integration

require_once 'config.php';

class LogManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Mencatat pertanyaan pengguna ke dalam log
     */
    public function logQuestion($userQuestion, $aiResponse, $videoUrl = null, $userId = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO question_logs (user_question, ai_response, matched_keyword, matched_video_url, tafsir_data, ip_address, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $keywords = $this->extractKeywords($userQuestion);
            $tafsirData = json_encode([]); // Dalam implementasi nyata, ini akan berisi data tafsir
            
            $stmt->execute([
                $userQuestion, 
                $aiResponse, 
                $keywords, 
                $videoUrl, 
                $tafsirData, 
                $ipAddress, 
                $userId
            ]);
            
            logActivity("Question logged", $userId);
        } catch (Exception $e) {
            error_log("Error logging question: " . $e->getMessage());
            logError("Error logging question: " . $e->getMessage(), "User ID: $userId");
            // Continue execution even if logging fails
        }
    }
    
    /**
     * Mencatat pertanyaan pengguna terdaftar
     */
    public function logUserQuestion($userId, $question, $answer, $quranReference = null, $videoUrl = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_questions (user_id, question, answer, quran_reference, video_url) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$userId, $question, $answer, $quranReference, $videoUrl]);
        } catch (Exception $e) {
            error_log("Error logging user question: " . $e->getMessage());
            logError("Error logging user question: " . $e->getMessage(), "User ID: $userId");
        }
    }
    
    /**
     * Menyimpan bookmark pertanyaan oleh pengguna
     */
    public function saveBookmark($userId, $questionId) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_bookmarks (user_id, question_id) 
                VALUES (?, ?)
            ");
            
            $stmt->execute([$userId, $questionId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error saving bookmark: " . $e->getMessage());
            logError("Error saving bookmark: " . $e->getMessage(), "User ID: $userId");
            return false;
        }
    }
    
    /**
     * Mendapatkan riwayat pertanyaan pengguna
     */
    public function getUserQuestionHistory($userId, $limit = 10, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, question, answer, quran_reference, video_url, created_at 
                FROM user_questions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user history: " . $e->getMessage());
            logError("Error getting user history: " . $e->getMessage(), "User ID: $userId");
            return [];
        }
    }
    
    /**
     * Mendapatkan daftar bookmark pengguna
     */
    public function getUserBookmarks($userId, $limit = 10, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("
                SELECT uq.id, uq.question, uq.answer, uq.quran_reference, uq.created_at 
                FROM user_bookmarks ub
                JOIN user_questions uq ON ub.question_id = uq.id
                WHERE ub.user_id = ?
                ORDER BY ub.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user bookmarks: " . $e->getMessage());
            logError("Error getting user bookmarks: " . $e->getMessage(), "User ID: $userId");
            return [];
        }
    }
    
    /**
     * Mengekstrak keyword dari pertanyaan
     */
    private function extractKeywords($question) {
        $words = explode(' ', strtolower($question));
        $keywords = array_filter($words, function($word) {
            return strlen($word) > 3; // Hanya menyertakan kata yang lebih dari 3 karakter
        });
        
        return implode(', ', array_slice($keywords, 0, 5)); // Maksimal 5 keyword
    }
    
    /**
     * Mendapatkan statistik penggunaan
     */
    public function getUsageStats() {
        try {
            // Jumlah total pertanyaan
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM question_logs");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Jumlah pertanyaan per hari
            $stmt = $this->conn->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM question_logs GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
            $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Pertanyaan paling sering
            $stmt = $this->conn->query("
                SELECT user_question, COUNT(*) as frequency 
                FROM question_logs 
                GROUP BY user_question 
                ORDER BY frequency DESC 
                LIMIT 5
            ");
            $frequentQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total_questions' => $total,
                'daily_stats' => $dailyStats,
                'frequent_questions' => $frequentQuestions
            ];
        } catch (Exception $e) {
            error_log("Error getting stats: " . $e->getMessage());
            logError("Error getting stats: " . $e->getMessage());
            return [
                'total_questions' => 0,
                'daily_stats' => [],
                'frequent_questions' => []
            ];
        }
    }
    
    /**
     * Menghapus log pertanyaan lama (opsional, untuk manajemen penyimpanan)
     */
    public function cleanupOldLogs($days = 30) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM question_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $result = $stmt->execute([$days]);
            
            $deletedCount = $stmt->rowCount();
            logActivity("Cleaned up $deletedCount old logs", null);
            
            return $deletedCount;
        } catch (Exception $e) {
            error_log("Error cleaning up logs: " . $e->getMessage());
            logError("Error cleaning up logs: " . $e->getMessage());
            return 0;
        }
    }
}

// Contoh penggunaan:
/*
$logManager = new LogManager();

// Mencatat pertanyaan
$logManager->logQuestion("Apa yang dikatakan Al-Quran tentang penciptaan manusia?", "Penjelasan dari AI...");

// Mencatat pertanyaan pengguna terdaftar
$logManager->logUserQuestion(1, "Apa hukumnya jika tidak sholat?", "Menurut pendapat mayoritas ulama...", "QS. Al-Baqarah: 238");

// Mendapatkan riwayat pengguna
$history = $logManager->getUserQuestionHistory(1);
*/
?>