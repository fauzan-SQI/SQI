<?php
// server/getAnswer_fallback.php - API endpoint with fallback for Science-Qur'an Integration

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

require_once 'db_config_fallback.php';
require_once 'keyword_matcher.php';
require_once 'log_manager.php';
require_once 'video_config.php';

// Function to sanitize input
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}

// Function to get answer from database or use fallback
if (!function_exists('getAnswer')) {
    function getAnswer($question) {
        try {
            // Sanitize and validate input
            $question = sanitizeInput($question);
            if (empty($question) || strlen($question) > 500) {
                return [
                    'error' => 'Invalid question parameter'
                ];
            }
            
            // Check if database is available
            if (isDatabaseAvailable()) {
                // Database is available, try to get from DB
                return getAnswerFromDatabase($question);
            } else {
                // Database not available, use fallback with valid video reference as text
                return [
                    'id' => 0,
                    'question_keywords' => $question,
                    'answer_text' => 'Saat ini server database tidak dapat diakses. Berikut adalah jawaban standar:\n\n' . getFallbackResponse($question),
                    'quran_reference' => 'QS. Al-Baqarah: 2 - ذَٰلِكَ الْكِتَٰبُ لَا رَيْبَ ۛ فِيهِ ۛ هُدًى لِّلْمُتَّقِينَ',
                    'video_reference' => getFallbackVideo('https://www.youtube.com/embed/dQw4w9WgXcQ', 'general'),
                    'youtube_link' => '', // Clear embedded video link
                    'tafsir_data' => [],
                    'database_status' => 'unavailable'
                ];
            }
            
        } catch(Exception $e) {
            error_log("General error in getAnswer: " . $e->getMessage());
            return [
                'error' => 'An error occurred while processing your request'
            ];
        }
    }
}

// Function to get answer from database when available
if (!function_exists('getAnswerFromDatabase')) {
    function getAnswerFromDatabase($question) {
        try {
            $conn = getConnection();
            
            // First, try exact match on the full question
            $stmt = $conn->prepare("SELECT id, question_keywords, answer_text, quran_reference, youtube_link FROM answers WHERE question_keywords LIKE ? ORDER BY id DESC LIMIT 1");
            $fullQuestionSearch = '%'.strtolower($question).'%';
            $stmt->execute([$fullQuestionSearch]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Validate the result before returning
                $result['answer_text'] = htmlspecialchars($result['answer_text'], ENT_QUOTES, 'UTF-8');
                $result['quran_reference'] = htmlspecialchars($result['quran_reference'], ENT_QUOTES, 'UTF-8');
                $result['youtube_link'] = filter_var($result['youtube_link'], FILTER_VALIDATE_URL) ?: '';
                $result['tafsir_data'] = []; // Placeholder, would be populated in full implementation
                $result['database_status'] = 'available';
                
                // Instead of embedding video, provide video link as text for reference only
                if (!empty($result['youtube_link'])) {
                    $result['video_reference'] = $result['youtube_link'];
                    $result['youtube_link'] = ''; // Clear embedded video link
                }
                
                return $result;
            }
            
            // If no exact match, try to match individual words in the question
            $questionWords = extractKeywordsFallback(strtolower($question));
            
            if (count($questionWords) > 0) {
                // Try to find answers that contain any of the keywords from the question
                $orConditions = [];
                $params = [];
                
                foreach ($questionWords as $word) {
                    if (strlen($word) > 2) { // Only use words longer than 2 characters
                        $orConditions[] = "question_keywords LIKE ?";
                        $params[] = '%' . $word . '%';
                    }
                }
                
                if (!empty($orConditions)) {
                    $sql = "SELECT id, question_keywords, answer_text, quran_reference, youtube_link,
                            COUNT(*) as keyword_matches
                            FROM answers 
                            WHERE " . implode(' OR ', $orConditions) . "
                            GROUP BY id
                            ORDER BY keyword_matches DESC, id DESC
                            LIMIT 1";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result) {
                        // Validate the result before returning
                        $result['answer_text'] = htmlspecialchars($result['answer_text'], ENT_QUOTES, 'UTF-8');
                        $result['quran_reference'] = htmlspecialchars($result['quran_reference'], ENT_QUOTES, 'UTF-8');
                        $result['youtube_link'] = filter_var($result['youtube_link'], FILTER_VALIDATE_URL) ?: '';
                        $result['tafsir_data'] = [];
                        $result['database_status'] = 'available';
                        
                        // Instead of embedding video, provide video link as text for reference only
                        if (!empty($result['youtube_link'])) {
                            $result['video_reference'] = $result['youtube_link'];
                            $result['youtube_link'] = ''; // Clear embedded video link
                        }
                        
                        return $result;
                    }
                }
            }
            
            // If still no match, try a broader search
            $broaderSearch = [];
            foreach ($questionWords as $word) {
                if (strlen($word) > 2) {
                    $broaderSearch[] = $word;
                }
            }
            
            if (!empty($broaderSearch)) {
                // Try to find partial matches that have high relevance
                $partialSearch = '%' . implode('%', $broaderSearch) . '%';
                $stmt = $conn->prepare("SELECT id, question_keywords, answer_text, quran_reference, youtube_link FROM answers WHERE question_keywords LIKE ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$partialSearch]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    // Validate the result before returning
                    $result['answer_text'] = htmlspecialchars($result['answer_text'], ENT_QUOTES, 'UTF-8');
                    $result['quran_reference'] = htmlspecialchars($result['quran_reference'], ENT_QUOTES, 'UTF-8');
                    $result['youtube_link'] = filter_var($result['youtube_link'], FILTER_VALIDATE_URL) ?: '';
                    $result['tafsir_data'] = [];
                    $result['database_status'] = 'available';
                    
                    // Instead of embedding video, provide video link as text for reference only
                    // Validate YouTube URL and get fallback if necessary
                    if (!empty($result['youtube_link'])) {
                        $result['video_reference'] = getFallbackVideo($result['youtube_link'], 'general');
                        $result['youtube_link'] = ''; // Clear embedded video link
                    }
                    
                    return $result;
                }
            }
            
            // If no match found in DB, return fallback
            return [
                'id' => 0,
                'question_keywords' => $question,
                'answer_text' => getFallbackResponse($question),
                'quran_reference' => 'QS. Al-Anbiya: 30 - وَجَعَلْنَا مِنَ الْمَاءِ كُلَّ شَيْءٍ حَيٍّ أَفَلَا يُؤْمِنُونَ',
                'youtube_link' => '',
                'video_reference' => '', // No video reference for fallback
                'tafsir_data' => [],
                'database_status' => 'available_no_result'
            ]; // No result found in DB
            
        } catch(PDOException $e) {
            error_log("Database error in getAnswerFromDatabase: " . $e->getMessage());
            // If database error occurs, use fallback
            return [
                'id' => 0,
                'question_keywords' => $question,
                'answer_text' => 'Terjadi kesalahan pada database: ' . $e->getMessage() . '\n\n' . getFallbackResponse($question),
                'quran_reference' => 'QS. Al-Baqarah: 2',
                'youtube_link' => '',
                'video_reference' => '', // No video reference for database error
                'tafsir_data' => [],
                'database_status' => 'error'
            ];
        }
    }
}

// Helper function to extract keywords from text for fallback
if (!function_exists('extractKeywordsFallback')) {
    function extractKeywordsFallback($text) {
        // Remove common stop words that don't contribute to meaning
        $stopWords = ['dan', 'atau', 'dengan', 'dari', 'untuk', 'pada', 'di', 'ke', 'yang', 'apa', 'bagaimana', 'mengapa', 'siapa', 'kapan', 'dimana', 'apakah', 'apabila', 'jika', 'karena', 'sehingga', 'maka', 'tetapi', 'namun', 'sedangkan', 'sambil', 'sejak', 'selagi', 'selama', 'sinyal', 'sementara', 'daripada', 'tentang', 'hingga', 'sampai', 'kecuali', 'sebesar', 'semua', 'setiap', 'sedikit', 'beberapa', 'banyak', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'oleh', 'adalah', 'merupakan', 'ialah', 'akan', 'telah', 'sudah', 'masih', 'belum', 'juga', 'hanya', 'memang', 'agak', 'amat', 'sangat', 'terlalu', 'terlampau', 'terlantas', 'terus', 'makin', 'semakin', 'terkira', 'sepertinya', 'kayaknya', 'laksana', 'bagai', 'seolah', 'seakan', 'ibarat', 'seakan-akan', 'seolah-olah', 'bagaikan', 'bagai', 'laksana', 'kata', 'menurut', 'atas', 'dari', 'semenjak', 'sejak', 'sampai', 'hingga', 'sampai-sampai', 'maksud', 'guna', 'untuk', 'supaya', 'asal', 'daripada', 'sebelum', 'sehabis', 'sesudah', 'setelah', 'sejak', 'semenjak', 'sedari', 'demi', 'selama', 'sepanjang', 'sampai', 'hingga', 'kecuali', 'melainkan', 'kecuali', 'bahwasanya', 'sebetulnya', 'sebenarnya', 'sesungguhnya', 'sering', 'seringnya', 'kadang', 'kadang-kadang', 'kali', 'sekali', 'saja', 'saling', 'sama', 'bersama', 'setiap', 'semua', 'seluruh', 'tiap', 'masing', 'masing-masing', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'satu-satu', 'dua-dua', 'bagai', 'ibarat', 'laksana', 'seakan', 'seolah', 'kata', 'perkata', 'kalimat', 'ujar', 'firmankan', 'berita', 'kabar', 'beritakan', 'pada', 'kepada', 'untuk', 'tentang', 'mengenai', 'diberi', 'dikasih', 'diberikan', 'baca', 'membaca', 'membaca', 'telah', 'sudah', 'akan', 'dapat', 'dapatkah', 'bisa', 'bisakah', 'boleh', 'bolehkah', 'mampu', 'mampukah', 'mungkin', 'mungkinkah'];
        
        $words = preg_split('/\s+/', $text);
        $keywords = [];
        
        foreach ($words as $word) {
            $word = trim($word, " \t\n\r\0\x0B.,!?;:\"'()");
            if ($word && !in_array(strtolower($word), $stopWords) && strlen($word) > 2) {
                $keywords[] = strtolower($word);
            }
        }
        
        return array_unique($keywords);
    }
}

// Function to generate fallback response
if (!function_exists('getFallbackResponse')) {
    function getFallbackResponse($question) {
        $responses = [
            "Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan mencocokkan kata kunci dari pertanyaan Anda dengan database yang berisi penjelasan ilmiah dan ayat Al-Qur'an terkait. Server database saat ini sedang tidak dapat diakses, mohon periksa kembali konfigurasi server Anda.",
            "Jawaban untuk pertanyaan Anda akan segera tersedia. Sistem sedang mengalami kendala teknis pada server database. Mohon coba kembali dalam beberapa saat.",
            "Terima kasih atas pertanyaan Anda tentang sains dan Al-Qur'an. Server database saat ini sedang dalam perawatan. Silakan kembali lagi nanti untuk mendapatkan jawaban lengkap."
        ];
        
        return $responses[array_rand($responses)];
    }
}

// Process request
$question = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $question = $input['question'] ?? $_POST['question'] ?? '';
} else {
    $question = $_GET['question'] ?? '';
}

// Sanitize the question parameter
$question = sanitizeInput($question);

if (!empty($question)) {
    $answer = getAnswer($question);
    echo json_encode($answer);
} else {
    echo json_encode(['error' => 'Question parameter is required']);
}

?>