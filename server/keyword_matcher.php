<?php
// keyword_matcher.php - Matches user questions with relevant videos

require_once 'config.php';

class KeywordMatcher {
    public function findRelevantVideos($userQuestion) {
        try {
            $conn = getConnection();
            
            // Convert question to lowercase for matching
            $question = strtolower($userQuestion);
            
            // Prepare to search for keywords in the question
            $keywords = $this->extractKeywords($question);
            $relevantVideos = [];
            
            // First, try to match exact keyword phrases
            if (count($keywords) > 0) {
                $orConditions = [];
                $params = [];
                
                foreach ($keywords as $keyword) {
                    if (strlen($keyword) > 2) { // Only use words longer than 2 characters
                        $orConditions[] = "keyword LIKE ?";
                        $params[] = '%' . $keyword . '%';
                    }
                }
                
                if (!empty($orConditions)) {
                    $sql = "SELECT id, keyword, title, youtube_url, description,
                            COUNT(*) as keyword_matches
                            FROM videos 
                            WHERE " . implode(' OR ', $orConditions) . "
                            GROUP BY id
                            ORDER BY keyword_matches DESC, id DESC
                            LIMIT 10";  // Get top 10 matches to later filter by relevance
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Calculate relevance based on how many keywords match
                        $relevance = $this->calculateRelevanceMultiple($row['keyword'], $keywords);
                        $row['relevance'] = $relevance;
                        
                        // Validate YouTube URL and get fallback if necessary
                        $row['youtube_url'] = getFallbackVideo($row['youtube_url'], 'general');
                        
                        $relevantVideos[] = $row;
                    }
                }
            }
            
            // If no keyword matches found, try to get top videos as fallback
            if (empty($relevantVideos)) {
                $stmt = $conn->prepare("SELECT id, keyword, title, youtube_url, description, 0 as relevance FROM videos ORDER BY id DESC LIMIT 5");
                $stmt->execute();
                $relevantVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Validate YouTube URLs for fallback videos
                foreach ($relevantVideos as &$video) {
                    $video['youtube_url'] = getFallbackVideo($video['youtube_url'], 'general');
                }
            }
            
            // Sort by relevance
            usort($relevantVideos, function($a, $b) {
                return $b['relevance'] - $a['relevance'];
            });
            
            // Return top 3 most relevant videos
            return array_slice($relevantVideos, 0, 3);
            
        } catch (Exception $e) {
            error_log("Error in keyword matching: " . $e->getMessage());
            return [];
        }
    }
    
    private function extractKeywords($question) {
        // Remove common stop words that don't contribute to meaning
        $stopWords = ['dan', 'atau', 'dengan', 'dari', 'untuk', 'pada', 'di', 'ke', 'yang', 'apa', 'bagaimana', 'mengapa', 'siapa', 'kapan', 'dimana', 'apakah', 'apabila', 'jika', 'karena', 'sehingga', 'maka', 'tetapi', 'namun', 'sedangkan', 'sambil', 'sejak', 'selagi', 'selama', 'sinyal', 'sementara', 'daripada', 'tentang', 'hingga', 'sampai', 'kecuali', 'sebesar', 'semua', 'setiap', 'sedikit', 'beberapa', 'banyak', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'oleh', 'adalah', 'merupakan', 'ialah', 'akan', 'telah', 'sudah', 'masih', 'belum', 'juga', 'hanya', 'memang', 'agak', 'amat', 'sangat', 'terlalu', 'terlampau', 'terlantas', 'terus', 'makin', 'semakin', 'terkira', 'sepertinya', 'kayaknya', 'laksana', 'bagai', 'seolah', 'seakan', 'ibarat', 'seakan-akan', 'seolah-olah', 'bagaikan', 'bagai', 'laksana', 'kata', 'menurut', 'atas', 'dari', 'semenjak', 'sejak', 'sampai', 'hingga', 'sampai-sampai', 'maksud', 'guna', 'untuk', 'supaya', 'asal', 'daripada', 'sebelum', 'sehabis', 'sesudah', 'setelah', 'sejak', 'semenjak', 'sedari', 'demi', 'selama', 'sepanjang', 'sampai', 'hingga', 'kecuali', 'melainkan', 'kecuali', 'bahwasanya', 'sebetulnya', 'sebenarnya', 'sesungguhnya', 'sering', 'seringnya', 'kadang', 'kadang-kadang', 'kali', 'sekali', 'saja', 'saling', 'sama', 'bersama', 'setiap', 'semua', 'seluruh', 'tiap', 'masing', 'masing-masing', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'satu-satu', 'dua-dua', 'bagai', 'ibarat', 'laksana', 'seakan', 'seolah', 'kata', 'perkata', 'kalimat', 'ujar', 'firmankan', 'berita', 'kabar', 'beritakan', 'pada', 'kepada', 'untuk', 'tentang', 'mengenai', 'diberi', 'dikasih', 'diberikan', 'baca', 'membaca', 'membaca', 'telah', 'sudah', 'akan', 'dapat', 'dapatkah', 'bisa', 'bisakah', 'boleh', 'bolehkah', 'mampu', 'mampukah', 'mungkin', 'mungkinkah'];
        
        $words = preg_split('/\s+/', $question);
        $keywords = [];
        
        foreach ($words as $word) {
            $word = trim($word, " \t\n\r\0\x0B.,!?;:\"'()");
            if ($word && !in_array(strtolower($word), $stopWords) && strlen($word) > 2) {
                $keywords[] = strtolower($word);
            }
        }
        
        return array_unique($keywords);
    }
    
    private function calculateRelevance($keyword, $question) {
        // Simple relevance calculation based on how often keyword appears in question
        return substr_count($question, $keyword);
    }
    
    private function calculateRelevanceMultiple($videoKeyword, $questionKeywords) {
        // Calculate relevance based on how many keywords match
        $relevance = 0;
        $videoKeywordLower = strtolower($videoKeyword);
        
        foreach ($questionKeywords as $qk) {
            if (strlen($qk) > 2) {
                // Check if the keyword appears in the video keyword
                if (strpos($videoKeywordLower, $qk) !== false) {
                    $relevance += 2; // Bonus for substring match
                }
                
                // Check if the keyword matches exactly
                if (strtolower($videoKeyword) === $qk) {
                    $relevance += 5; // Higher bonus for exact match
                }
                
                // Check if the keyword is similar to the video keyword
                $similarity = similar_text($qk, $videoKeywordLower, $percent);
                if ($percent > 60) {
                    $relevance += 1; // Small bonus for similar keywords
                }
            }
        }
        
        return $relevance;
    }
    
    public function getTopVideos($limit = 10) {
        try {
            $conn = getConnection();
            
            $stmt = $conn->prepare("
                SELECT id, keyword, title, youtube_url, description 
                FROM videos 
                ORDER BY id DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting top videos: " . $e->getMessage());
            return [];
        }
    }
    
    public function getVideoById($id) {
        try {
            $conn = getConnection();
            
            $stmt = $conn->prepare("
                SELECT id, keyword, title, youtube_url, description 
                FROM videos 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting video by ID: " . $e->getMessage());
            return null;
        }
    }
}

// Example usage:
// $matcher = new KeywordMatcher();
// $relevantVideos = $matcher->findRelevantVideos("Apa yang dikatakan Al-Qur'an tentang penciptaan manusia?");
// print_r($relevantVideos);
?>