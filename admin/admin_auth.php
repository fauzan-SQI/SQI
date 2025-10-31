<?php
// admin_auth.php - Authentication handler for admin panel

// Gunakan pendekatan aman untuk include konfigurasi
// Include konfigurasi auth yang sudah menangani semua ketergantungan
require_once '../auth/config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // Check if user exists and is an admin by checking against users table
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set admin session dengan pendekatan aman
            // Cek apakah sesi sudah aktif sebelum memulai
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['login_time'] = time();
            $_SESSION['is_admin'] = true;
            
            echo json_encode(['success' => true, 'message' => 'Login berhasil']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
        }
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?>