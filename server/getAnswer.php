<?php
// API endpoint to retrieve answers from database or AI, with video matching and tafsir integration

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

require_once 'db_config.php';
require_once 'ai_handler.php';
require_once 'keyword_matcher.php';
require_once 'log_manager.php';
require_once 'video_config.php';

// Function to sanitize input - only define if not already defined
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}

// Function to get answer from database or AI - only define if not already defined
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
            
            // First, try to find a match in the existing database
            $dbAnswer = getAnswerFromDB($question);
            
            // If we found a result in the DB, use it
            if ($dbAnswer && !isset($dbAnswer['error'])) {
                // Find relevant videos for this question
                $keywordMatcher = new KeywordMatcher();
                $relevantVideos = $keywordMatcher->findRelevantVideos($question);
                
                // Use the first relevant video if available
                $videoUrl = !empty($relevantVideos) ? $relevantVideos[0]['youtube_url'] : $dbAnswer['youtube_link'];
                
                // Validate video URL and get fallback if necessary
                $videoCategory = (strpos(strtolower($dbAnswer['question_keywords']), 'penciptaan') !== false || strpos(strtolower($dbAnswer['question_keywords']), 'manusia') !== false) ? 'creation' : 'general';
                $videoUrl = getFallbackVideo($videoUrl, $videoCategory);
                
                // Instead of embedding video, provide video link as text for reference only
                $dbAnswer['video_reference'] = $videoUrl;
                $dbAnswer['youtube_link'] = ''; // Clear embedded video link
                
                // Log the question with matched video
                $logManager = new LogManager();
                $logManager->logQuestion($question, $dbAnswer['answer_text'], $videoUrl);
                
                return $dbAnswer;
            }
            
            // If no DB match or we want to try AI, call the AI
            try {
                $aiHandler = new AIHandler();
                $aiResult = $aiHandler->getAIResponse($question);
                
                // The AI response now includes tafsir data
                $aiResponse = $aiResult['response'];
                $tafsirData = $aiResult['tafsir_data'];
                
                // Find relevant videos for this question
                $keywordMatcher = new KeywordMatcher();
                $relevantVideos = $keywordMatcher->findRelevantVideos($question);
                
                // Use the first relevant video if available
                $videoUrl = !empty($relevantVideos) ? $relevantVideos[0]['youtube_url'] : '';
                
                // Validate video URL and get fallback if necessary
                $videoUrl = getFallbackVideo($videoUrl, 'general');
                
                // Instead of embedding video, provide video link as text for reference only
                $response = [
                    'id' => 0, // Indicating this is from AI
                    'question_keywords' => $question,
                    'answer_text' => $aiResponse,
                    'quran_reference' => '', // AI-generated response doesn't have a specific verse, but AI might include it
                    'video_reference' => $videoUrl, // Provide video link as reference only
                    'youtube_link' => '', // Clear embedded video link
                    'tafsir_data' => $tafsirData // Include tafsir data in response
                ];
                
                return $response;
            } catch (Exception $e) {
                error_log("AI error: " . $e->getMessage());
                
                // If AI fails, return default response with valid fallback video
                return [
                    'id' => 0,
                    'question_keywords' => $question,
                    'answer_text' => 'Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan mencocokkan kata kunci dari pertanyaan Anda dengan database yang berisi penjelasan ilmiah dan ayat Al-Qur\'an terkait.',
                    'quran_reference' => 'QS. An-Nahl: 10 - هوَ الَّذِي أَنزَلَ مِنَ السَّمَاءِ مَاءً لَّكُم مِّنْهُ شَرَابٌ وَمِنْهُ شَجَرٌ فِيهِ تَسِيمُونَ',
                    'youtube_link' => getFallbackVideo('https://www.youtube.com/embed/abcd1234', 'general'),
                    'tafsir_data' => [] // No tafsir data if AI fails
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

// Helper function to get answer from database - only define if not already defined
if (!function_exists('getAnswerFromDB')) {
    function getAnswerFromDB($question) {
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
                return $result;
            }
            
            // If no exact match, try to match individual words in the question
            $questionWords = extractKeywords(strtolower($question));
            
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
                        
                        // Instead of embedding video, provide video link as text for reference only
                        // Validate YouTube URL and get fallback if necessary
                        if (!empty($result['youtube_link'])) {
                            $result['video_reference'] = getFallbackVideo($result['youtube_link'], 'general');
                            $result['youtube_link'] = ''; // Clear embedded video link
                        }
                        
                        return $result;
                    }
                }
            }
            
            // If still no match, try a broader search with more flexible matching
            $broaderSearch = [];
            foreach ($questionWords as $word) {
                if (strlen($word) > 2) {
                    $broaderSearch[] = $word;
                }
            }
            
            if (!empty($broaderSearch)) {
                // Try to find partial matches that have high relevance
                $searchTerm = implode(' ', $broaderSearch);
                $stmt = $conn->prepare("SELECT id, question_keywords, answer_text, quran_reference, youtube_link FROM answers WHERE MATCH(question_keywords) AGAINST(? IN BOOLEAN MODE) OR question_keywords LIKE ? ORDER BY id DESC LIMIT 1");
                // Note: For MySQL match query to work properly, you'd need a fulltext index on question_keywords
                // As fallback, we'll use LIKE with the combined keywords
                $partialSearch = '%' . implode('%', $broaderSearch) . '%';
                $stmt->execute([$searchTerm, $partialSearch]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    // Validate the result before returning
                    $result['answer_text'] = htmlspecialchars($result['answer_text'], ENT_QUOTES, 'UTF-8');
                    $result['quran_reference'] = htmlspecialchars($result['quran_reference'], ENT_QUOTES, 'UTF-8');
                    $result['youtube_link'] = filter_var($result['youtube_link'], FILTER_VALIDATE_URL) ?: '';
                    return $result;
                }
            }
            
            return null; // No result found in DB
            
        } catch(PDOException $e) {
            error_log("Database error in getAnswerFromDB: " . $e->getMessage());
            return null;
        }
    }
}

// Helper function to extract keywords from text
if (!function_exists('extractKeywords')) {
    function extractKeywords($text) {
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