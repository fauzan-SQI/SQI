<?php
// auth/profile.php - Halaman profil pengguna

// Menggunakan pendekatan aman untuk include konfigurasi
// Include konfigurasi otentikasi yang sudah menangani semua ketergantungan dengan aman
require_once 'config.php';

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Ambil informasi pengguna dari database
$user_info = null;
$question_history = [];
$bookmarks = [];

try {
    $conn = getConnection();
    $user_id = getCurrentUserId();
    
    // Ambil informasi pengguna
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ambil riwayat pertanyaan
    $stmt = $conn->prepare("
        SELECT id, question, answer, quran_reference, video_url, created_at 
        FROM user_questions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $question_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil bookmark
    $stmt = $conn->prepare("
        SELECT uq.question, uq.answer, uq.quran_reference, uq.created_at 
        FROM user_bookmarks ub
        JOIN user_questions uq ON ub.question_id = uq.id
        WHERE ub.user_id = ?
        ORDER BY ub.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Profile error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan dalam mengambil data profil";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Science-Qur'an Integration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }
        
        .profile-header h1 {
            color: #001f3f;
            margin: 0;
        }
        
        .logout-btn {
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .user-info h3 {
            color: #001f3f;
            margin-top: 0;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-item strong {
            color: #001f3f;
            display: block;
        }
        
        .detail-item span {
            color: #666;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .section-header h2 {
            color: #001f3f;
            margin: 0;
        }
        
        .history-item, .bookmark-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #001f3f;
        }
        
        .history-item h4, .bookmark-item h4 {
            margin: 0 0 8px 0;
            color: #001f3f;
        }
        
        .history-item p, .bookmark-item p {
            margin: 5px 0;
            color: #555;
        }
        
        .history-item .timestamp, .bookmark-item .timestamp {
            font-size: 0.85rem;
            color: #888;
        }
        
        .history-item .quran-ref, .bookmark-item .quran-ref {
            color: #f1c40f;
            font-style: italic;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> Profil Pengguna</h1>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
        <div class="user-info">
            <h3><i class="fas fa-info-circle"></i> Informasi Akun</h3>
            <div class="user-details">
                <div class="detail-item">
                    <strong>Username</strong>
                    <span><?php echo htmlspecialchars($user_info['username'] ?? 'Tidak tersedia'); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Email</strong>
                    <span><?php echo htmlspecialchars($user_info['email'] ?? 'Tidak tersedia'); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Tanggal Bergabung</strong>
                    <span><?php echo date('d M Y', strtotime($user_info['created_at'] ?? '')); ?></span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Riwayat Pertanyaan</h2>
            </div>
            <?php if (empty($question_history)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada riwayat pertanyaan</p>
                </div>
            <?php else: ?>
                <?php foreach ($question_history as $question): ?>
                    <div class="history-item">
                        <h4><?php echo htmlspecialchars($question['question']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($question['answer'], 0, 150)) . '...'; ?></p>
                        <?php if (!empty($question['quran_reference'])): ?>
                            <p class="quran-ref"><i class="fas fa-book-quran"></i> <?php echo htmlspecialchars($question['quran_reference']); ?></p>
                        <?php endif; ?>
                        <div class="timestamp">
                            <i class="far fa-clock"></i> <?php echo date('d M Y, H:i', strtotime($question['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-bookmark"></i> Bookmark</h2>
            </div>
            <?php if (empty($bookmarks)): ?>
                <div class="empty-state">
                    <i class="fas fa-bookmark"></i>
                    <p>Belum ada bookmark</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookmarks as $bookmark): ?>
                    <div class="bookmark-item">
                        <h4><?php echo htmlspecialchars($bookmark['question']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($bookmark['answer'], 0, 150)) . '...'; ?></p>
                        <?php if (!empty($bookmark['quran_reference'])): ?>
                            <p class="quran-ref"><i class="fas fa-book-quran"></i> <?php echo htmlspecialchars($bookmark['quran_reference']); ?></p>
                        <?php endif; ?>
                        <div class="timestamp">
                            <i class="far fa-clock"></i> <?php echo date('d M Y, H:i', strtotime($bookmark['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="../opening.html" style="color: #001f3f; text-decoration: none; font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
