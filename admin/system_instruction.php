<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Instruction - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #001f3f, #0a3d62);
            color: white;
            padding: 20px 0;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar h3 {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin: 5px 0;
        }
        
        .sidebar-menu a {
            display: block;
            color: #ddd;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: #f1c40f;
            border-left: 4px solid #f1c40f;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f5f7fa;
        }
        
        .header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #001f3f;
            margin: 0;
            font-size: 1.5rem;
        }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .section-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            color: #001f3f;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #333;
            font-size: 1.1rem;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 250px;
            font-family: monospace;
            font-size: 1rem;
            line-height: 1.5;
            resize: vertical;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
        }
        
        .instructions-preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #001f3f;
            margin-top: 20px;
        }
        
        .instructions-preview h4 {
            color: #001f3f;
            margin-top: 0;
        }
        
        .instructions-preview p {
            margin: 5px 0;
            color: #555;
        }
        
        .default-instruction {
            color: #f1c40f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3><i class="fas fa-book-quran"></i> SQI Admin</h3>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="api_settings.php"><i class="fas fa-key"></i> API Settings</a></li>
                <li><a href="video_manager.php"><i class="fas fa-video"></i> Video Manager</a></li>
                <li><a href="#" class="active"><i class="fas fa-robot"></i> System Instruction</a></li>
                <li><a href="question_logs.php"><i class="fas fa-comments"></i> Question Logs</a></li>
                <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>System Instruction</h1>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-robot"></i> Konfigurasi Instruksi Sistem AI</h2>
                </div>
                
                <p>Atur instruksi sistem yang akan digunakan oleh model AI untuk menjawab pertanyaan pengguna. Instruksi ini menentukan bagaimana AI akan merespons dan format jawaban yang diharapkan.</p>
                
                <div class="form-group">
                    <label for="system-instruction">System Instruction untuk AI</label>
                    <textarea id="system-instruction">Anda adalah Pakar Integrasi Sains dan Al-Qur'an. Jawaban harus berisi: 1. Penjelasan ilmiah. 2. Dalil Al-Qur'an. 3. Analisis integrasi. Tolak pertanyaan non-relevan dengan sopan.

Petunjuk Rinci:
- Fokus pada hubungan antara konsep ilmiah dan ayat-ayat Al-Qur'an
- Sertakan ayat spesifik dengan terjemahan
- Berikan penjelasan ilmiah yang akurat dan mutakhir
- Jika pertanyaan di luar cakupan, arahkan ke topik terkait dengan sains dan Al-Qur'an
- Gunakan bahasa yang mudah dipahami oleh pelajar umum
- Jika tidak yakin, akui keterbatasan dan sarankan sumber tambahan</textarea>
                    
                    <button class="btn-primary" onclick="saveSystemInstruction()" style="margin-top: 15px;">
                        <i class="fas fa-save"></i> Simpan Instruksi
                    </button>
                </div>
                
                <div class="instructions-preview">
                    <h4><i class="fas fa-lightbulb"></i> Preview Instruksi Sistem</h4>
                    <p><span class="default-instruction">Peran AI:</span> Pakar Integrasi Sains dan Al-Qur'an</p>
                    <p><span class="default-instruction">Format Jawaban:</span></p>
                    <p>1. Penjelasan ilmiah terkini</p>
                    <p>2. Dalil Al-Qur'an (ayat dan terjemahan)</p>
                    <p>3. Analisis integrasi antara sains dan ayat</p>
                    <p><span class="default-instruction">Cakupan:</span> Pertanyaan tentang sains dan Al-Qur'an</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function saveSystemInstruction() {
            const instruction = document.getElementById('system-instruction').value;
            
            if (!instruction.trim()) {
                alert('Mohon masukkan instruksi sistem');
                return;
            }
            
            // In a real implementation, send data to server
            if (confirm('Apakah Anda yakin ingin menyimpan instruksi sistem ini?')) {
                alert('System instruction berhasil disimpan!');
                
                // In a real implementation, you would save to the database
                // For now, just show success message
            }
        }
        
        // Auto-save warning
        document.getElementById('system-instruction').addEventListener('input', function() {
            // Add a visual indicator that changes have been made
            document.querySelector('.btn-primary').innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
        });
    </script>
</body>
</html>