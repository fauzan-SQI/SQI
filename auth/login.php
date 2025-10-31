<?php
// auth/login.php - Halaman login pengguna

// Menggunakan pendekatan aman untuk include konfigurasi
// Include konfigurasi otentikasi yang sudah menangani semua ketergantungan dengan aman
require_once 'config.php';

// Jika pengguna sudah login, arahkan ke halaman profil
if (isLoggedIn()) {
    header("Location: profile.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        try {
            $conn = getConnection();
            
            // Cari pengguna berdasarkan username
            $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login berhasil
                startUserSession($user['id'], $user['username']);
                
                // Log aktivitas login
                logLoginActivity($user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                
                // Arahkan ke halaman profil
                header("Location: profile.php");
                exit();
            } else {
                $error_message = "Username atau password salah";
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan dalam proses login";
            error_log("Login error: " . $e->getMessage());
        }
    } else {
        $error_message = "Silakan masukkan username dan password";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Science-Qur'an Integration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }
        
        .login-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 30px;
            text-align: center;
        }
        
        .login-box h2 {
            color: #001f3f;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .login-box h2 i {
            color: #f1c40f;
            margin-right: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #001f3f;
            box-shadow: 0 0 0 2px rgba(0, 31, 63, 0.1);
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .register-link {
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: #001f3f;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2><i class="fas fa-book-quran"></i> SQI Login</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username atau Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Login <i class="fas fa-sign-in-alt"></i></button>
            </form>
            
            <div class="register-link">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="../opening.html" style="color: #001f3f; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>