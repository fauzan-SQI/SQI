<?php
// video_config.php - Konfigurasi video default untuk Science-Qur'an Integration v2.1

// Video default yang digunakan ketika tidak ada kecocokan atau video tidak valid
// Mulai v2.1, video tidak lagi di-embed otomatis melainkan ditampilkan sebagai link teks
define('DEFAULT_SCIENCE_VIDEO', 'https://www.youtube.com/embed/Wyf7i83T8jU');
define('DEFAULT_CREATION_VIDEO', 'https://www.youtube.com/embed/9vYuaubrOmo');
define('DEFAULT_GENERAL_VIDEO', 'https://www.youtube.com/embed/6th5hNj2CQ0');

// Video placeholder yang digunakan sebagai referensi teks
define('PLACEHOLDER_SCIENCE_VIDEO', 'https://www.youtube.com/embed/Wyf7i83T8jU');
define('PLACEHOLDER_CREATION_VIDEO', 'https://www.youtube.com/embed/9vYuaubrOmo');
define('PLACEHOLDER_GENERAL_VIDEO', 'https://www.youtube.com/embed/6th5hNj2CQ0');

// Fungsi untuk memvalidasi URL YouTube
function isValidYouTubeUrl($url) {
    // Cek apakah URL adalah format YouTube yang valid (baik watch maupun embed)
    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(embed\/|watch\?v=)|youtu\.be\/)[\w-]+/i';
    return preg_match($pattern, $url);
}

// Fungsi untuk mengkonversi URL YouTube ke format embed jika perlu
function convertToEmbedUrl($url) {
    if (strpos($url, 'youtube.com/watch') !== false) {
        // Konversi dari format watch ke embed
        $url = str_replace('watch?v=', 'embed/', $url);
        $url = str_replace('youtube.com/', 'youtube.com/embed/', $url);
        $url = preg_replace('/&.*/', '', $url); // hapus parameter tambahan
    } elseif (strpos($url, 'youtu.be/') !== false) {
        // Konversi dari format short link ke embed
        $url = str_replace('youtu.be/', 'youtube.com/embed/', $url);
    }
    
    return $url;
}

// Fungsi untuk mengkonversi URL YouTube ke format watch untuk link teks referensi
function convertToWatchUrl($url) {
    // Ekstrak ID video dari URL embed
    $videoIdMatch = [];
    if (preg_match('/(?:embed\/|v=|watch\?v=)([a-zA-Z0-9_-]{11})/', $url, $videoIdMatch)) {
        if (isset($videoIdMatch[1])) {
            return 'https://www.youtube.com/watch?v=' . $videoIdMatch[1];
        }
    }
    return $url; // Kembalikan URL asli jika tidak dapat diekstrak
}

// Fungsi untuk mendapatkan video fallback yang valid
function getFallbackVideo($primaryUrl = '', $category = 'general', $format = 'embed') {
    $validatedUrl = '';
    
    if (!empty($primaryUrl) && isValidYouTubeUrl($primaryUrl)) {
        $validatedUrl = convertToEmbedUrl($primaryUrl);
    }
    
    if (empty($validatedUrl)) {
        switch ($category) {
            case 'science':
                $validatedUrl = DEFAULT_SCIENCE_VIDEO;
                break;
            case 'creation':
                $validatedUrl = DEFAULT_CREATION_VIDEO;
                break;
            case 'general':
            default:
                $validatedUrl = DEFAULT_GENERAL_VIDEO;
                break;
        }
    }
    
    if ($format === 'watch') {
        return convertToWatchUrl($validatedUrl);
    }
    
    return $validatedUrl;
}

function getFallbackVideoWatchUrl($primaryUrl = '', $category = 'general') {
    return getFallbackVideo($primaryUrl, $category, 'watch');
}

// Daftar video cadangan yang diketahui valid
$verifiedVideoUrls = [
    'https://www.youtube.com/embed/9vYuaubrOmo',  // Proses Pembentukan Janin
    'https://www.youtube.com/embed/Wyf7i83T8jU',  // Teori Big Bang
    'https://www.youtube.com/embed/vRJvVf-Vg3k',  // Peran Air dalam Kehidupan
    'https://www.youtube.com/embed/6th5hNj2CQ0',  // Sistem Tata Surya
    'https://www.youtube.com/embed/2E2zX6dFHM4',  // Struktur dan Fungsi Gunung
    'https://www.youtube.com/embed/3A3JTi2KUZ0',  // Cahaya dan Matahari
    'https://www.youtube.com/embed/fS-1gwFhiK0',  // Fungsi Otak Manusia
];
?>
