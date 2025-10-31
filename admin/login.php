<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Science-Qur'an Integration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #001f3f 0%, #0a3d62 100%);
            padding: 20px;
        }
        
        .admin-login-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .admin-login-box h2 {
            color: #001f3f;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .admin-login-box h2 i {
            color: #f1c40f;
            margin-right: 10px;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #001f3f;
        }
        
        .btn-login {
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #e74c3c;
            margin-top: 15px;
            font-size: 0.9rem;
            display: none;
        }
        
        .logo-container {
            margin-bottom: 25px;
        }
        
        .logo-container i {
            font-size: 3.5rem;
            color: #f1c40f;
            background: linear-gradient(135deg, #001f3f, #0a3d62);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="logo-container">
                <i class="fas fa-lock"></i>
            </div>
            <h2><i class="fas fa-user-shield"></i> Admin Panel Login</h2>
            <p style="color: #666; margin-bottom: 25px;">Masukkan kredensial admin untuk mengakses panel kontrol</p>
            
            <form id="loginForm">
                <div class="input-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username admin" required>
                </div>
                
                <div class="input-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
                
                <div id="errorMessage" class="error-message">
                    Username atau password salah. Silakan coba lagi.
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Simple client-side validation
            if (!username || !password) {
                showMessage('Mohon lengkapi username dan password', true);
                return;
            }
            
            // Send credentials to server for verification
            showMessage('Memproses login...', false, true);
            
            // Send request to server
            fetch('admin_auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Login berhasil! Mengarahkan ke dashboard...', false);
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showMessage(data.message || 'Username atau password salah. Silakan coba lagi.', true);
                }
            })
            .catch(error => {
                showMessage('Terjadi kesalahan saat login. Silakan coba lagi.', true);
            });
        });
        
        function showMessage(message, isError = false, isLoading = false) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.style.color = isError ? '#e74c3c' : '#27ae60';
            
            if (isLoading) {
                errorDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + message;
            }
        }
    </script>
</body>
</html>