<?php
// server/api/daily_fact.php - API endpoint untuk fakta sains harian

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config.php';

// Function to get daily fact
function getDailyFact() {
    try {
        $conn = getConnection();
        
        // Dapatkan tanggal hari ini
        $today = date('Y-m-d');
        
        // Coba cari fakta untuk hari ini
        $stmt = $conn->prepare("SELECT fact_text, quran_reference FROM daily_facts WHERE created_at = ? AND is_active = 1");
        $stmt->execute([$today]);
        $fact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Jika tidak ada fakta untuk hari ini, ambil secara acak
        if (!$fact) {
            $stmt = $conn->prepare("SELECT fact_text, quran_reference FROM daily_facts WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $fact = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Jika tetap tidak ada fakta, gunakan fakta default
        if (!$fact) {
            $fact = [
                'fact_text' => 'Di dalam Al-Qur\'an terdapat banyak ayat yang menjelaskan fenomena alam dan sains. Aplikasi Science-Qur\'an Integration membantu Anda menemukan hubungan antara pengetahuan sains modern dan ayat-ayat Al-Qur\'an.',
                'quran_reference' => 'QS. Al-Mulk: 3 - Dan Dialah yang menciptakan langit dan bumi dalam seisinya, dan Kami melangitkan langit itu dengan beberapa bintang, dan Kami menjaganya dari setiap syaitan yang berontak.'
            ];
        }
        
        return $fact;
        
    } catch (Exception $e) {
        error_log("Daily fact error: " . $e->getMessage());
        return [
            'fact_text' => 'Fakta sains harian tidak tersedia saat ini. Aplikasi Science-Qur\'an Integration membantu Anda memahami hubungan antara sains modern dan ayat-ayat Al-Qur\'an.',
            'quran_reference' => 'QS. An-Nahl: 10 - Dia-lah yang menurunkan air dari langit, sebagian untuk minum dan sebagian (lagi) untuk (menumbuhkan) tumbuh-tumbuhan yang kamu ternakkan.'
        ];
    }
}

// Process request
$fact = getDailyFact();
echo json_encode($fact);
?>