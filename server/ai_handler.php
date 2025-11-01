<?php
// ai_handler.php - Handles requests to the AI API (e.g., Gemini)

require_once __DIR__ . '/bootstrap.php';
require_once 'tafsir_service.php';

class AIHandler {
    private $apiKey;
    private $apiUrl;
    private $systemInstruction;
    private $tafsirService;

    public function __construct() {
        // Get API configuration from database
        $this->loadAPIConfig();
        
        // Initialize tafsir service
        $this->tafsirService = new TafsirService();
        
        // Set API URL for Gemini
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    }

    private function loadAPIConfig() {
        try {
            $conn = getConnection();
            
            // Get the active API key
            $stmt = $conn->prepare("SELECT api_key FROM api_keys WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->apiKey = $result['api_key'];
            } else {
                throw new Exception("No active API key found");
            }
            
            // Get system instruction
            $stmt = $conn->prepare("SELECT instruction_text FROM system_instructions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->systemInstruction = $result['instruction_text'];
            } else {
                // Use default instruction
                $this->systemInstruction = "Anda adalah Pakar Integrasi Sains dan Al-Qur'an. Jawaban harus berisi: 1. Penjelasan ilmiah. 2. Dalil Al-Qur'an lengkap dengan terjemahan. 3. Tafsir ringkas dari ayat tersebut. 4. Analisis integrasi antara sains dan ayat. Tolak pertanyaan non-relevan dengan sopan.";
            }
        } catch (Exception $e) {
            error_log("Error loading API config: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAIResponse($userQuestion) {
        if (!$this->apiKey) {
            throw new Exception("API key not configured");
        }
        
        // Prepare the prompt with system instruction
        $prompt = $this->systemInstruction . "\n\nPertanyaan: " . $userQuestion . "\n\nBerikan jawaban dalam format terstruktur dengan penjelasan ilmiah, ayat Al-Qur'an yang relevan lengkap dengan terjemahan, tafsir ringkas, dan analisis integrasi sains dengan ayat tersebut.";
        
        $postData = array(
            'contents' => array(
                'parts' => array(
                    array('text' => $prompt)
                )
            )
        );
        
        $jsonData = json_encode($postData);
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '?key=' . $this->apiKey);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API Error: HTTP $httpCode - " . $response);
        }
        
        $responseData = json_decode($response, true);
        
        if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Invalid API response format");
        }
        
        $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Get relevant tafsir for the question
        $tafsirData = $this->tafsirService->getTafsirForQuestion($userQuestion);
        
        // Log the question and response
        $this->logQuestion($userQuestion, $aiResponse, $tafsirData);
        
        return [
            'response' => $aiResponse,
            'tafsir_data' => $tafsirData
        ];
    }
    
    private function logQuestion($userQuestion, $aiResponse, $tafsirData = []) {
        try {
            $conn = getConnection();
            
            // Find relevant video based on keywords
            $videoUrl = $this->findRelevantVideo($userQuestion);
            
            // Serialize tafsir data for storage
            $tafsirJson = json_encode($tafsirData);
            
            $stmt = $conn->prepare("
                INSERT INTO question_logs (user_question, ai_response, matched_keyword, matched_video_url, tafsir_data, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->execute([$userQuestion, $aiResponse, $this->extractKeywords($userQuestion), $videoUrl, $tafsirJson, $ipAddress]);
        } catch (Exception $e) {
            error_log("Error logging question: " . $e->getMessage());
            // Continue execution even if logging fails
        }
    }
    
    private function findRelevantVideo($question) {
        try {
            $conn = getConnection();
            
            // This is a simple keyword matching approach
            // In a real implementation, you might want to use more sophisticated matching
            $keywords = explode(' ', strtolower($question));
            
            foreach ($keywords as $keyword) {
                if (strlen($keyword) > 3) { // Only consider keywords longer than 3 characters
                    $stmt = $conn->prepare("SELECT youtube_url FROM videos WHERE keyword LIKE ? ORDER BY id DESC LIMIT 1");
                    $searchKeyword = '%' . $keyword . '%';
                    $stmt->execute([$searchKeyword]);
                    
                    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        return $result['youtube_url'];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error finding relevant video: " . $e->getMessage());
        }
        
        return null;
    }
    
    private function extractKeywords($question) {
        // Simple keyword extraction - in a real implementation, you'd want more sophisticated NLP
        $words = explode(' ', strtolower($question));
        $keywords = array_filter($words, function($word) {
            return strlen($word) > 3; // Only include words longer than 3 characters
        });
        
        return implode(', ', array_slice($keywords, 0, 5)); // Return first 5 keywords
    }
    
    public function getTafsirService() {
        return $this->tafsirService;
    }
}

// Example usage:
// $aiHandler = new AIHandler();
// $result = $aiHandler->getAIResponse("Apa yang dikatakan Al-Qur'an tentang penciptaan manusia?");
// echo $result['response'];
?>
