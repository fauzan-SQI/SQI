<?php
// auth/register.php - Halaman registrasi pengguna

// Menggunakan pendekatan aman untuk include konfigurasi
// Include konfigurasi otentikasi yang sudah menangani semua ketergantungan dengan aman
require_once 'config.php';

// Jika pengguna sudah login, arahkan ke halaman profil
if (isLoggedIn()) {
    header("Location: profile.php");
    exit();
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field harus diisi";
    } elseif ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email tidak valid";
    } else {
        try {
            $conn = getConnection();
            
            // Cek apakah username atau email sudah digunakan
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error_message = "Username atau email sudah digunakan";
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_ARGON2ID);
                
                // Insert pengguna baru
                $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $result = $stmt->execute([$username, $email, $password_hash]);
                
                if ($result) {
                    $success_message = "Registrasi berhasil! Silakan login untuk melanjutkan.";
                } else {
                    $error_message = "Terjadi kesalahan saat registrasi";
                }
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan dalam proses registrasi";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Science-Qur'an Integration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }
        
        .register-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 30px;
            text-align: center;
        }
        
        .register-box h2 {
            color: #001f3f;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .register-box h2 i {
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
        
        .btn-register {
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
        
        .btn-register:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .success-message {
            color: #27ae60;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .login-link {
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #001f3f;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h2><i class="fas fa-user-plus"></i> Daftar Akun</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-register">Daftar <i class="fas fa-user-plus"></i></button>
            </form>
            
            <div class="login-link">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="../opening.html" style="color: #001f3c; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
