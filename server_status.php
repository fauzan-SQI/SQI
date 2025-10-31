<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Server - Science-Qur'an Integration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #f1c40f;
            font-size: 1.1rem;
        }
        
        .status-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .status-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .status-card.operational {
            border-top: 5px solid #27ae60;
        }
        
        .status-card.warning {
            border-top: 5px solid #f39c12;
        }
        
        .status-card.error {
            border-top: 5px solid #e74c3c;
        }
        
        .status-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .operational .status-icon {
            color: #27ae60;
        }
        
        .warning .status-icon {
            color: #f39c12;
        }
        
        .error .status-icon {
            color: #e74c3c;
        }
        
        .status-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .status-detail {
            font-size: 0.9rem;
            color: #666;
        }
        
        .checks-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .checks-header {
            background: #001f3f;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .checks-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .check-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .check-item:hover {
            background-color: #f8f9fa;
        }
        
        .check-status {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .status-success {
            background-color: #27ae60;
        }
        
        .status-warning {
            background-color: #f39c12;
        }
        
        .status-error {
            background-color: #e74c3c;
        }
        
        .check-content {
            flex: 1;
        }
        
        .check-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .check-message {
            font-size: 0.9rem;
            color: #666;
        }
        
        .refresh-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px 24px;
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .refresh-btn:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
            transform: translateY(-2px);
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            font-style: italic;
            color: #666;
        }
        
        .timestamp {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 20px;
        }
        
        .application-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            text-align: center;
            padding: 10px;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 500;
            color: #001f3f;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            
            .status-overview {
                grid-template-columns: 1fr;
            }
            
            .checks-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-server"></i> Status Server SQI</h1>
            <p>Science-Qur'an Integration - Sistem Monitoring</p>
        </div>
        
        <div class="application-info">
            <h3 style="margin-bottom: 15px; color: #001f3f; text-align: center;">Informasi Aplikasi</h3>
            <div class="info-grid" id="app-info">
                <div class="info-item">
                    <div class="info-label">Nama Aplikasi</div>
                    <div class="info-value" id="app-name">Memuat...</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Versi</div>
                    <div class="info-value" id="app-version">Memuat...</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Lingkungan</div>
                    <div class="info-value" id="app-environment">Memuat...</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status Keseluruhan</div>
                    <div class="info-value" id="overall-status">Memuat...</div>
                </div>
            </div>
        </div>
        
        <div class="status-overview" id="status-overview">
            <!-- Status cards will be populated by JavaScript -->
        </div>
        
        <div class="checks-container">
            <div class="checks-header">
                <span>Detail Pemeriksaan Sistem</span>
                <span id="summary-text">Memuat...</span>
            </div>
            <div class="checks-list" id="checks-list">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Memuat status sistem...
                </div>
            </div>
        </div>
        
        <button class="refresh-btn" id="refresh-btn">
            <i class="fas fa-sync-alt"></i> Periksa Ulang
        </button>
        
        <div class="timestamp" id="timestamp">
            Terakhir diperiksa: -
        </div>
    </div>

    <script>
        // Ambil status dari API
        async function fetchStatus() {
            try {
                const response = await fetch('server/status_check.php');
                const data = await response.json();
                
                displayStatus(data);
            } catch (error) {
                console.error('Error fetching status:', error);
                document.getElementById('checks-list').innerHTML = 
                    `<div class="check-item">
                        <div class="check-status status-error"></div>
                        <div class="check-content">
                            <div class="check-name">Gagal Memuat Status</div>
                            <div class="check-message">Error: ${error.message}</div>
                        </div>
                    </div>`;
            }
        }
        
        function displayStatus(data) {
            // Update application info
            document.getElementById('app-name').textContent = data.application_info.name || 'Tidak Tersedia';
            document.getElementById('app-version').textContent = data.application_info.version || 'Tidak Tersedia';
            document.getElementById('app-environment').textContent = data.application_info.environment || 'Tidak Tersedia';
            document.getElementById('overall-status').textContent = formatStatusText(data.server_status);
            
            // Update timestamp
            document.getElementById('timestamp').textContent = `Terakhir diperiksa: ${data.timestamp}`;
            
            // Update summary
            document.getElementById('summary-text').textContent = 
                `${data.summary.success_count} Sukses, ${data.summary.warning_count} Peringatan, ${data.summary.error_count} Error`;
            
            // Update status overview cards
            const statusOverview = document.getElementById('status-overview');
            statusOverview.innerHTML = `
                <div class="status-card ${getStatusClass(data.server_status)}">
                    <div class="status-icon">${getStatusIcon(data.server_status)}</div>
                    <div class="status-title">Status Keseluruhan</div>
                    <div class="status-detail">${formatStatusText(data.server_status)}</div>
                </div>
                <div class="status-card">
                    <div class="status-icon"><i class="fas fa-check-circle" style="color: #27ae60;"></i></div>
                    <div class="status-title">Sukses</div>
                    <div class="status-detail">${data.summary.success_count} pemeriksaan</div>
                </div>
                <div class="status-card">
                    <div class="status-icon"><i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i></div>
                    <div class="status-title">Peringatan</div>
                    <div class="status-detail">${data.summary.warning_count} isu</div>
                </div>
                <div class="status-card">
                    <div class="status-icon"><i class="fas fa-times-circle" style="color: #e74c3c;"></i></div>
                    <div class="status-title">Error</div>
                    <div class="status-detail">${data.summary.error_count} error</div>
                </div>
            `;
            
            // Update checks list
            const checksList = document.getElementById('checks-list');
            checksList.innerHTML = '';
            
            data.checks.forEach(check => {
                const checkItem = document.createElement('div');
                checkItem.className = 'check-item';
                checkItem.innerHTML = `
                    <div class="check-status status-${check.status}"></div>
                    <div class="check-content">
                        <div class="check-name">${check.name}</div>
                        <div class="check-message">${check.message}</div>
                        ${check.details ? `<div class="check-details" style="margin-top: 5px; font-size: 0.8rem; color: #888;">Detail: ${JSON.stringify(check.details)}</div>` : ''}
                    </div>
                `;
                checksList.appendChild(checkItem);
            });
        }
        
        function getStatusClass(status) {
            switch(status) {
                case 'operational': return 'operational';
                case 'warning': return 'warning';
                case 'error': return 'error';
                default: return '';
            }
        }
        
        function getStatusIcon(status) {
            switch(status) {
                case 'operational': return '<i class="fas fa-check-circle"></i>';
                case 'warning': return '<i class="fas fa-exclamation-triangle"></i>';
                case 'error': return '<i class="fas fa-times-circle"></i>';
                default: return '<i class="fas fa-question-circle"></i>';
            }
        }
        
        function formatStatusText(status) {
            switch(status) {
                case 'operational': return 'Operasional';
                case 'warning': return 'Peringatan';
                case 'error': return 'Error';
                default: return status.charAt(0).toUpperCase() + status.slice(1);
            }
        }
        
        // Refresh button event
        document.getElementById('refresh-btn').addEventListener('click', fetchStatus);
        
        // Load status on page load
        document.addEventListener('DOMContentLoaded', fetchStatus);
    </script>
</body>
</html>