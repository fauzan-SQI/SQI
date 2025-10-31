<?php
// server/tafsir_service.php - Service untuk mengambil tafsir Al-Qur'an

require_once 'config.php';

class TafsirService {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Mengambil tafsir berdasarkan nomor surah dan ayat
     */
    public function getTafsir($surahNumber, $ayahNumber) {
        try {
            $stmt = $this->conn->prepare("
                SELECT tafsir_source, tafsir_text 
                FROM tafsir 
                WHERE ayah_id = (
                    SELECT id FROM quran_ayahs 
                    WHERE surah_number = ? AND ayah_number = ?
                )
            ");
            $stmt->execute([$surahNumber, $ayahNumber]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Tafsir service error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mengambil tafsir berdasarkan keyword
     */
    public function getTafsirByKeyword($keyword) {
        try {
            $searchTerm = '%' . $keyword . '%';
            
            $stmt = $this->conn->prepare("
                SELECT t.tafsir_source, t.tafsir_text, q.surah_number, q.ayah_number
                FROM tafsir t
                JOIN quran_ayahs q ON t.ayah_id = q.id
                WHERE t.tafsir_text LIKE ? OR q.arabic_text LIKE ? OR q.translation LIKE ?
                LIMIT 5
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Tafsir service keyword search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mencari ayat relevan berdasarkan keyword
     */
    public function findRelevantAyah($keyword) {
        try {
            $searchTerm = '%' . $keyword . '%';
            
            $stmt = $this->conn->prepare("
                SELECT id, surah_number, ayah_number, arabic_text, translation, transliteration
                FROM quran_ayahs
                WHERE arabic_text LIKE ? OR translation LIKE ? OR transliteration LIKE ?
                LIMIT 3
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Ayah search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Menggabungkan tafsir dengan ayat yang relevan
     */
    public function getTafsirForQuestion($question) {
        // Pecah pertanyaan menjadi kata kunci
        $keywords = explode(' ', $question);
        
        // Cari ayat yang relevan
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 3) { // Hanya ambil kata yang lebih dari 3 karakter
                $ayahs = $this->findRelevantAyah($keyword);
                
                if (!empty($ayahs)) {
                    // Untuk setiap ayat yang ditemukan, ambil tafsirnya
                    $result = [];
                    foreach ($ayahs as $ayah) {
                        $tafsir = $this->getTafsir($ayah['surah_number'], $ayah['ayah_number']);
                        $result[] = [
                            'ayah' => $ayah,
                            'tafsir' => $tafsir
                        ];
                    }
                    
                    if (!empty($result)) {
                        return $result;
                    }
                }
            }
        }
        
        return [];
    }
}

// Contoh penggunaan:
/*
$tafsirService = new TafsirService();
$tafsir = $tafsirService->getTafsirForQuestion("pembentukan janin dalam Al-Qur'an");
print_r($tafsir);
*/
?>