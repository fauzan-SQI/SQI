<?php
require_once 'admin_check.php';
// Gunakan pendekatan aman untuk include konfigurasi
// Include konfigurasi auth yang sudah menangani semua ketergantungan
require_once '../auth/config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Science-Qur'an Integration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-dashboard {
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
        
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: #001f3f;
            margin-bottom: 15px;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #001f3f;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            color: #001f3f;
            margin: 0;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #001f3f;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .api-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="sidebar">
            <h3><i class="fas fa-book-quran"></i> SQI Admin</h3>
            <ul class="sidebar-menu">
                <li><a href="#" class="active" onclick="showSection('dashboard')"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" onclick="showSection('api-settings')"><i class="fas fa-key"></i> API Settings</a></li>
                <li><a href="#" onclick="showSection('video-manager')"><i class="fas fa-video"></i> Video Manager</a></li>
                <li><a href="#" onclick="showSection('system-instruction')"><i class="fas fa-robot"></i> System Instruction</a></li>
                <li><a href="#" onclick="showSection('question-logs')"><i class="fas fa-comments"></i> Question Logs</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard Admin</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <span><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin User'); ?></span>
                </div>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-comments"></i>
                    <div class="number" id="total-questions">142</div>
                    <div class="label">Total Pertanyaan</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-video"></i>
                    <div class="number" id="total-videos">28</div>
                    <div class="label">Video Tersedia</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-key"></i>
                    <div class="number" id="api-keys">1</div>
                    <div class="label">API Aktif</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="number" id="today-questions">12</div>
                    <div class="label">Hari Ini</div>
                </div>
            </div>
            
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> Ringkasan Sistem</h2>
                </div>
                <p>Selamat datang di panel administrasi Science-Qur'an Integration. Di sini Anda dapat mengelola:</p>
                <ul>
                    <li>Kunci API untuk integrasi AI</li>
                    <li>Database video pembelajaran</li>
                    <li>System instruction untuk model AI</li>
                    <li>Riwayat pertanyaan pengguna</li>
                </ul>
                <p>Gunakan menu di sebelah kiri untuk mengakses fitur administrasi.</p>
            </div>
            
            <!-- API Settings Section -->
            <div id="api-settings-section" class="content-section hidden">
                <div class="section-header">
                    <h2><i class="fas fa-key"></i> Pengaturan API</h2>
                    <button class="btn-primary" onclick="toggleApiForm()">
                        <i class="fas fa-plus"></i> Tambah API Key
                    </button>
                </div>
                
                <div id="api-form" class="hidden" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <h3>Tambah/Edit API Key</h3>
                    <div class="form-group">
                        <label>Nama API</label>
                        <input type="text" id="api-name" placeholder="Contoh: Gemini API">
                    </div>
                    <div class="form-group">
                        <label>API Key</label>
                        <input type="password" id="api-key" placeholder="Masukkan API key di sini">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="api-status">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <button class="btn-primary" onclick="saveApiKey()">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button class="btn-primary" style="background: #95a5a6; margin-left: 10px;" onclick="toggleApiForm()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama API</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gemini API</td>
                            <td><span class="api-status status-active">Aktif</span></td>
                            <td>2025-01-15</td>
                            <td class="actions">
                                <button class="btn-action btn-edit" onclick="editApiKey(1)"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn-action btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Video Manager Section -->
            <div id="video-manager-section" class="content-section hidden">
                <div class="section-header">
                    <h2><i class="fas fa-video"></i> Manajemen Video</h2>
                    <button class="btn-primary" onclick="toggleVideoForm()">
                        <i class="fas fa-plus"></i> Tambah Video
                    </button>
                </div>
                
                <div id="video-form" class="hidden" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <h3>Tambah/Edit Video</h3>
                    <div class="form-group">
                        <label>Kata Kunci</label>
                        <input type="text" id="video-keyword" placeholder="Contoh: penciptaan manusia">
                    </div>
                    <div class="form-group">
                        <label>Judul Video</label>
                        <input type="text" id="video-title" placeholder="Judul video">
                    </div>
                    <div class="form-group">
                        <label>URL YouTube</label>
                        <input type="text" id="video-url" placeholder="https://www.youtube.com/embed/...">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea id="video-description" placeholder="Deskripsi video"></textarea>
                    </div>
                    <button class="btn-primary" onclick="saveVideo()">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button class="btn-primary" style="background: #95a5a6; margin-left: 10px;" onclick="toggleVideoForm()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Kata Kunci</th>
                            <th>Judul</th>
                            <th>URL</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>penciptaan manusia</td>
                            <td>Proses Penciptaan Manusia Menurut Sains dan Al-Qur'an</td>
                            <td><a href="#" target="_blank">Lihat</a></td>
                            <td>2025-01-15</td>
                            <td class="actions">
                                <button class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn-action btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                        <tr>
                            <td>alam semesta</td>
                            <td>Big Bang dan Penciptaan Alam Semesta dalam Al-Qur'an</td>
                            <td><a href="#" target="_blank">Lihat</a></td>
                            <td>2025-01-14</td>
                            <td class="actions">
                                <button class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn-action btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- System Instruction Section -->
            <div id="system-instruction-section" class="content-section hidden">
                <div class="section-header">
                    <h2><i class="fas fa-robot"></i> System Instruction</h2>
                </div>
                
                <div class="form-group">
                    <label>System Instruction untuk AI</label>
                    <textarea id="system-instruction" placeholder="Masukkan instruksi sistem untuk AI di sini...">Anda adalah Pakar Integrasi Sains dan Al-Qur'an. Jawaban harus berisi: 1. Penjelasan ilmiah. 2. Dalil Al-Qur'an. 3. Analisis integrasi. Tolak pertanyaan non-relevan dengan sopan.</textarea>
                </div>
                
                <button class="btn-primary" onclick="saveSystemInstruction()">
                    <i class="fas fa-save"></i> Simpan Instruksi
                </button>
            </div>
            
            <!-- Question Logs Section -->
            <div id="question-logs-section" class="content-section hidden">
                <div class="section-header">
                    <h2><i class="fas fa-comments"></i> Riwayat Pertanyaan</h2>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pertanyaan</th>
                            <th>Video Terkait</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Apa yang dikatakan Al-Qur'an tentang penciptaan manusia?</td>
                            <td><a href="#" target="_blank">Lihat Video</a></td>
                            <td>2025-01-15 10:30</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Bagaimana Al-Qur'an menjelaskan tentang sistem tata surya?</td>
                            <td><a href="#" target="_blank">Lihat Video</a></td>
                            <td>2025-01-15 09:45</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Show selected section and hide others
        function showSection(sectionId) {
            // Hide all sections
            document.getElementById('dashboard-section').classList.add('hidden');
            document.getElementById('api-settings-section').classList.add('hidden');
            document.getElementById('video-manager-section').classList.add('hidden');
            document.getElementById('system-instruction-section').classList.add('hidden');
            document.getElementById('question-logs-section').classList.add('hidden');
            
            // Show selected section
            document.getElementById(sectionId + '-section').classList.remove('hidden');
            
            // Update active menu item
            document.querySelectorAll('.sidebar-menu a').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Load specific content based on section
            if (sectionId === 'question-logs') {
                loadQuestionLogs();
            } else if (sectionId === 'video-manager') {
                loadVideos();
            } else if (sectionId === 'api-settings') {
                loadApiKeys();
            } else if (sectionId === 'dashboard') {
                loadDashboardStats();
            }
        }
        
        // Load dashboard statistics
        async function loadDashboardStats() {
            try {
                // Load question statistics
                const statsResponse = await fetch('api/question_logs.php?stats=1');
                const stats = await statsResponse.json();
                
                if (stats) {
                    document.getElementById('total-questions').textContent = stats.total || 0;
                    document.getElementById('today-questions').textContent = stats.today || 0;
                }
                
                // Load video count
                const videosCountResponse = await fetch('api/videos.php?count=1');
                const videosCount = await videosCountResponse.json();
                document.getElementById('total-videos').textContent = videosCount.count || 0;
                
                // Load API keys count
                const apiKeysResponse = await fetch('api/api_keys.php');
                const apiKeys = await apiKeysResponse.json();
                if (Array.isArray(apiKeys)) {
                    document.getElementById('api-keys').textContent = apiKeys.length;
                }
                
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }
        
        // Toggle API form
        function toggleApiForm() {
            const form = document.getElementById('api-form');
            form.classList.toggle('hidden');
        }
        
        // Toggle video form
        function toggleVideoForm() {
            const form = document.getElementById('video-form');
            form.classList.toggle('hidden');
            
            // Clear form when showing
            if (!form.classList.contains('hidden')) {
                document.getElementById('video-keyword').value = '';
                document.getElementById('video-title').value = '';
                document.getElementById('video-url').value = '';
                document.getElementById('video-description').value = '';
                document.getElementById('video-form').removeAttribute('data-id');
            }
        }
        
        // Load videos when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadVideos();
        });
        
        // Load videos from API
        async function loadVideos() {
            try {
                const response = await fetch('api/videos.php');
                const videos = await response.json();
                
                const tbody = document.querySelector('#video-manager-section tbody');
                tbody.innerHTML = '';
                
                if (videos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Tidak ada video ditemukan</td></tr>';
                    return;
                }
                
                videos.forEach(video => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${video.keyword}</td>
                        <td>${video.title}</td>
                        <td><a href="${video.youtube_url}" target="_blank">Lihat</a></td>
                        <td>${video.created_at}</td>
                        <td class="actions">
                            <button class="btn-action btn-edit" onclick="editVideo('${video.id}')"><i class="fas fa-edit"></i> Edit</button>
                            <button class="btn-action btn-delete" onclick="deleteVideo('${video.id}')"><i class="fas fa-trash"></i> Hapus</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } catch (error) {
                console.error('Error loading videos:', error);
                alert('Gagal memuat data video');
            }
        }
        
        // Edit video
        async function editVideo(id) {
            try {
                const response = await fetch(`api/videos.php?id=${id}`);
                const video = await response.json();
                
                if (video) {
                    document.getElementById('video-keyword').value = video.keyword;
                    document.getElementById('video-title').value = video.title;
                    document.getElementById('video-url').value = video.youtube_url;
                    document.getElementById('video-description').value = video.description || '';
                    
                    // Store the ID for update operation
                    document.getElementById('video-form').setAttribute('data-id', id);
                    toggleVideoForm();
                }
            } catch (error) {
                console.error('Error fetching video:', error);
                alert('Gagal mengambil data video');
            }
        }
        
        // Save video
        async function saveVideo() {
            const keyword = document.getElementById('video-keyword').value;
            const title = document.getElementById('video-title').value;
            const url = document.getElementById('video-url').value;
            const description = document.getElementById('video-description').value;
            const form = document.getElementById('video-form');
            const id = form.getAttribute('data-id');
            
            if (!keyword || !title || !url) {
                alert('Mohon lengkapi semua field yang diperlukan');
                return;
            }
            
            try {
                if (id) {
                    // Update existing video
                    const response = await fetch(`api/videos.php?id=${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            keyword: keyword,
                            title: title,
                            youtube_url: url,
                            description: description
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Video berhasil diperbarui!');
                        toggleVideoForm();
                        loadVideos();
                    } else {
                        alert('Gagal memperbarui video: ' + (result.error || 'Unknown error'));
                    }
                } else {
                    // Create new video
                    const response = await fetch('api/videos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            keyword: keyword,
                            title: title,
                            youtube_url: url,
                            description: description
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Video berhasil disimpan!');
                        toggleVideoForm();
                        loadVideos();
                    } else {
                        alert('Gagal menyimpan video: ' + (result.error || 'Unknown error'));
                    }
                }
            } catch (error) {
                console.error('Error saving video:', error);
                alert('Gagal menyimpan video: ' + error.message);
            }
        }
        
        // Delete video
        async function deleteVideo(id) {
            if (confirm('Apakah Anda yakin ingin menghapus video ini?')) {
                try {
                    const response = await fetch(`api/videos.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Video berhasil dihapus!');
                        loadVideos();
                    } else {
                        alert('Gagal menghapus video: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting video:', error);
                    alert('Gagal menghapus video: ' + error.message);
                }
            }
        }
        
        // Edit API key (mock function)
        function editApiKey(id) {
            document.getElementById('api-name').value = 'Gemini API';
            document.getElementById('api-key').value = '••••••••••••••••';
            document.getElementById('api-status').value = '1';
            toggleApiForm();
        }
        
        // Save API key (mock function)
        function saveApiKey() {
            alert('API Key berhasil disimpan!');
            toggleApiForm();
        }
        
        // Load system instruction when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadSystemInstruction();
        });
        
        // Load active system instruction
        async function loadSystemInstruction() {
            try {
                const response = await fetch('api/instructions.php?active=1');
                const instruction = await response.json();
                
                if (instruction) {
                    document.getElementById('system-instruction').value = instruction.instruction_text;
                }
            } catch (error) {
                console.error('Error loading system instruction:', error);
            }
        }
        
        // Save system instruction
        async function saveSystemInstruction() {
            const instructionText = document.getElementById('system-instruction').value;
            
            if (!instructionText) {
                alert('Mohon masukkan instruksi sistem');
                return;
            }
            
            try {
                // First, check if there's an existing active instruction to update
                const activeResponse = await fetch('api/instructions.php?active=1');
                const activeInstruction = await activeResponse.json();
                
                if (activeInstruction && activeInstruction.id) {
                    // Update existing active instruction
                    const response = await fetch(`api/instructions.php?id=${activeInstruction.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            instruction_name: 'Default AI Instruction',
                            instruction_text: instructionText,
                            is_active: 1
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('System instruction berhasil diperbarui!');
                    } else {
                        alert('Gagal memperbarui instruction: ' + (result.error || 'Unknown error'));
                    }
                } else {
                    // Create a new instruction and make it active
                    const response = await fetch('api/instructions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            instruction_name: 'Default AI Instruction',
                            instruction_text: instructionText,
                            is_active: 1
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('System instruction berhasil disimpan!');
                    } else {
                        alert('Gagal menyimpan instruction: ' + (result.error || 'Unknown error'));
                    }
                }
            } catch (error) {
                console.error('Error saving system instruction:', error);
                alert('Gagal menyimpan system instruction: ' + error.message);
            }
        }
        
        // Load question logs when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set default active menu item
            document.querySelector('.sidebar-menu a').classList.add('active');
            
            // Load question logs if we're on that section
            if (window.location.hash === '#question-logs' || document.querySelector('.sidebar-menu a[href*="question-logs"]')?.classList.contains('active')) {
                loadQuestionLogs();
            }
        });
        
        // Load question logs from API
        async function loadQuestionLogs() {
            try {
                const response = await fetch('api/question_logs.php?recent=1&limit=50');
                const logs = await response.json();
                
                const tbody = document.querySelector('#question-logs-section tbody');
                tbody.innerHTML = '';
                
                if (logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Tidak ada riwayat pertanyaan ditemukan</td></tr>';
                    return;
                }
                
                logs.forEach(log => {
                    const row = document.createElement('tr');
                    // Truncate question if too long
                    const truncatedQuestion = log.user_question.length > 50 ? 
                        log.user_question.substring(0, 50) + '...' : log.user_question;
                    
                    row.innerHTML = `
                        <td>${log.id}</td>
                        <td title="${log.user_question}">${truncatedQuestion}</td>
                        <td>${log.matched_video_url ? `<a href="${log.matched_video_url}" target="_blank">Lihat Video</a>` : 'Tidak ada'}</td>
                        <td>${new Date(log.created_at).toLocaleString('id-ID')}</td>
                    `;
                    tbody.appendChild(row);
                });
            } catch (error) {
                console.error('Error loading question logs:', error);
                alert('Gagal memuat riwayat pertanyaan');
            }
        }
    </script>
</body>
</html>