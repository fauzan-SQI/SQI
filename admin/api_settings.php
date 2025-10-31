<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Settings - Admin Panel</title>
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
        
        .form-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .form-group input[type="password"] {
            font-family: monospace;
        }
        
        .hidden {
            display: none;
        }
        
        .test-result {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        
        .test-success {
            background: #d4edda;
            color: #155724;
        }
        
        .test-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3><i class="fas fa-book-quran"></i> SQI Admin</h3>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" class="active"><i class="fas fa-key"></i> API Settings</a></li>
                <li><a href="video_manager.php"><i class="fas fa-video"></i> Video Manager</a></li>
                <li><a href="system_instruction.php"><i class="fas fa-robot"></i> System Instruction</a></li>
                <li><a href="question_logs.php"><i class="fas fa-comments"></i> Question Logs</a></li>
                <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Pengaturan API</h1>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-key"></i> Kelola API Keys</h2>
                    <button class="btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Tambah API Key
                    </button>
                </div>
                
                <div id="add-form" class="form-container hidden">
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
                    <button class="btn-primary" style="background: #95a5a6; margin-left: 10px;" onclick="hideAddForm()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button class="btn-primary" style="background: #3498db; margin-left: 10px;" onclick="testApiKey()">
                        <i class="fas fa-check-circle"></i> Uji API
                    </button>
                    <div id="test-result" class="test-result"></div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama API</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Diubah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="api-table-body">
                        <tr>
                            <td>Gemini API</td>
                            <td><span class="api-status status-active">Aktif</span></td>
                            <td>2025-01-15</td>
                            <td>2025-01-15</td>
                            <td>
                                <button class="btn-primary" onclick="editApiKey(1)" style="background: #3498db; padding: 5px 10px; font-size: 0.85rem; margin-right: 5px;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-primary" onclick="deleteApiKey(1)" style="background: #e74c3c; padding: 5px 10px; font-size: 0.85rem;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Load API keys when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadApiKeys();
        });
        
        function showAddForm() {
            document.getElementById('add-form').classList.remove('hidden');
            document.getElementById('api-name').value = '';
            document.getElementById('api-key').value = '';
            document.getElementById('api-status').value = '1';
            document.getElementById('test-result').classList.add('hidden');
        }
        
        function hideAddForm() {
            document.getElementById('add-form').classList.add('hidden');
        }
        
        async function loadApiKeys() {
            try {
                const response = await fetch('api/api_keys.php');
                const apiKeys = await response.json();
                
                const tableBody = document.getElementById('api-table-body');
                tableBody.innerHTML = '';
                
                if (apiKeys.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Tidak ada API key ditemukan</td></tr>';
                    return;
                }
                
                apiKeys.forEach(key => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${key.api_name}</td>
                        <td><span class="api-status ${key.is_active == 1 ? 'status-active' : 'status-inactive'}">${key.is_active == 1 ? 'Aktif' : 'Tidak Aktif'}</span></td>
                        <td>${key.created_at}</td>
                        <td>${key.updated_at}</td>
                        <td>
                            <button class="btn-primary" onclick="editApiKey('${key.id}')" style="background: #3498db; padding: 5px 10px; font-size: 0.85rem; margin-right: 5px;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-primary" onclick="deleteApiKey('${key.id}')" style="background: #e74c3c; padding: 5px 10px; font-size: 0.85rem;">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Error loading API keys:', error);
                alert('Gagal memuat data API keys');
            }
        }
        
        async function editApiKey(id) {
            try {
                const response = await fetch(`api/api_keys.php?id=${id}`);
                const apiKey = await response.json();
                
                if (apiKey) {
                    document.getElementById('api-name').value = apiKey.api_name;
                    document.getElementById('api-key').value = ''; // Don't show actual key for security
                    document.getElementById('api-status').value = apiKey.is_active;
                    document.getElementById('add-form').classList.remove('hidden');
                    
                    // Store the ID for update operation
                    document.getElementById('add-form').setAttribute('data-id', id);
                }
            } catch (error) {
                console.error('Error fetching API key:', error);
                alert('Gagal mengambil data API key');
            }
        }
        
        async function saveApiKey() {
            const name = document.getElementById('api-name').value;
            const key = document.getElementById('api-key').value;
            const status = document.getElementById('api-status').value;
            const form = document.getElementById('add-form');
            const id = form.getAttribute('data-id');
            
            if (!name) {
                alert('Mohon lengkapi nama API');
                return;
            }
            
            try {
                if (id) {
                    // Update existing API key
                    const data = {
                        api_name: name,
                        is_active: status
                    };
                    
                    // Only include key if it's being changed
                    if (key) {
                        data.api_key = key;
                    }
                    
                    const response = await fetch(`api/api_keys.php?id=${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('API Key berhasil diperbarui!');
                        hideAddForm();
                        loadApiKeys();
                    } else {
                        alert('Gagal memperbarui API Key: ' + (result.error || 'Unknown error'));
                    }
                } else {
                    // Create new API key
                    if (!key) {
                        alert('Mohon masukkan API Key');
                        return;
                    }
                    
                    const response = await fetch('api/api_keys.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            api_name: name,
                            api_key: key,
                            is_active: status
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('API Key berhasil disimpan!');
                        hideAddForm();
                        loadApiKeys();
                    } else {
                        alert('Gagal menyimpan API Key: ' + (result.error || 'Unknown error'));
                    }
                }
            } catch (error) {
                console.error('Error saving API key:', error);
                alert('Gagal menyimpan API Key: ' + error.message);
            }
        }
        
        async function deleteApiKey(id) {
            if (confirm('Apakah Anda yakin ingin menghapus API key ini?')) {
                try {
                    const response = await fetch(`api/api_keys.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('API Key berhasil dihapus!');
                        loadApiKeys();
                    } else {
                        alert('Gagal menghapus API Key: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting API key:', error);
                    alert('Gagal menghapus API Key: ' + error.message);
                }
            }
        }
        
        async function testApiKey() {
            const name = document.getElementById('api-name').value;
            const key = document.getElementById('api-key').value;
            
            if (!name || !key) {
                showTestResult('Mohon lengkapi nama API dan API Key untuk pengujian', 'error');
                return;
            }
            
            // Show testing message
            showTestResult('Menguji koneksi API...', 'info');
            
            try {
                // In a real implementation, this would test the actual API
                // For now, we just validate the format
                if (key.length >= 30) { // Basic validation
                    showTestResult('✅ Format API key valid. Koneksi API siap diuji secara langsung.', 'success');
                } else {
                    showTestResult('❌ Format API key tidak valid. Panjang minimal 30 karakter.', 'error');
                }
            } catch (error) {
                showTestResult('❌ Gagal menguji API: ' + error.message, 'error');
            }
        }
        
        function showTestResult(message, type) {
            const resultDiv = document.getElementById('test-result');
            resultDiv.textContent = message;
            resultDiv.classList.remove('hidden', 'test-success', 'test-error');
            
            if (type === 'success') {
                resultDiv.classList.add('test-success');
            } else if (type === 'error') {
                resultDiv.classList.add('test-error');
            }
            
            resultDiv.classList.remove('hidden');
        }
    </script>
</body>
</html>