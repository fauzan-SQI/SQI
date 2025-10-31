<?php
// server/quran_api.php - API untuk mengambil ayat dan tafsir Al-Qur'an

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';
require_once 'tafsir_service.php';
require_once 'video_config.php';

// Fungsi untuk mendapatkan ayat berdasarkan nomor surah dan ayat
function getAyah($surahNumber, $ayahNumber) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM quran_ayahs WHERE surah_number = ? AND ayah_number = ?");
        $stmt->execute([$surahNumber, $ayahNumber]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Ambil juga tafsir untuk ayat ini
            $tafsirService = new TafsirService();
            $tafsir = $tafsirService->getTafsir($surahNumber, $ayahNumber);
            $result['tafsir'] = $tafsir;
            
            return $result;
        } else {
            return null;
        }
    } catch (Exception $e) {
        error_log("Error getting ayah: " . $e->getMessage());
        return null;
    }
}

// Fungsi untuk mendapatkan beberapa ayat sekaligus
function getAyahRange($surahNumber, $fromAyah, $toAyah) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM quran_ayahs WHERE surah_number = ? AND ayah_number BETWEEN ? AND ? ORDER BY ayah_number");
        $stmt->execute([$surahNumber, $fromAyah, $toAyah]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tambahkan tafsir untuk setiap ayat
        $tafsirService = new TafsirService();
        foreach ($results as &$ayah) {
            $tafsir = $tafsirService->getTafsir($surahNumber, $ayah['ayah_number']);
            $ayah['tafsir'] = $tafsir;
        }
        
        return $results;
    } catch (Exception $e) {
        error_log("Error getting ayah range: " . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mencari ayat berdasarkan keyword
function searchAyah($keyword) {
    try {
        $conn = getConnection();
        
        $searchTerm = '%' . $keyword . '%';
        
        $stmt = $conn->prepare("
            SELECT id, surah_number, ayah_number, arabic_text, translation, transliteration
            FROM quran_ayahs
            WHERE arabic_text LIKE ? OR translation LIKE ? OR transliteration LIKE ?
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error searching ayah: " . $e->getMessage());
        return [];
    }
}

// Proses permintaan
$surah = $_GET['surah'] ?? null;
$ayah = $_GET['ayah'] ?? null;
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$search = $_GET['search'] ?? null;

// Validasi input
if (!is_numeric($surah) && !$search) {
    echo json_encode(['error' => 'Parameter surah atau search diperlukan']);
    exit;
}

if ($search) {
    // Mode pencarian
    $results = searchAyah($search);
    echo json_encode(['search_results' => $results]);
} else if ($from && $to) {
    // Mode rentang ayat
    if (!is_numeric($from) || !is_numeric($to)) {
        echo json_encode(['error' => 'Parameter from dan to harus berupa angka']);
        exit;
    }
    $results = getAyahRange($surah, $from, $to);
    echo json_encode(['surah' => $surah, 'ayah_range' => $results]);
} else if ($ayah) {
    // Mode satu ayat
    if (!is_numeric($ayah)) {
        echo json_encode(['error' => 'Parameter ayah harus berupa angka']);
        exit;
    }
    $result = getAyah($surah, $ayah);
    if ($result) {
        echo json_encode(['surah' => $surah, 'ayah' => $result]);
    } else {
        echo json_encode(['error' => 'Ayat tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'Parameter ayah diperlukan']);
}
?>