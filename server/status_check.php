<?php
// server/status_check.php - Sistem pengecekan status server untuk Science-Qur'an Integration

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once 'db_config_fallback.php';

class StatusChecker {
    private $checks = [];
    
    public function runAllChecks() {
        $this->checkPHPVersion();
        $this->checkDatabase();
        $this->checkRequiredExtensions();
        $this->checkFilePermissions();
        $this->checkAPIConnectivity();
        
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'server_status' => $this->getOverallStatus(),
            'checks' => $this->checks,
            'application_info' => [
                'name' => defined('APP_NAME') ? APP_NAME : 'Science-Qur\'an Integration',
                'version' => defined('APP_VERSION') ? APP_VERSION : '2.0',
                'environment' => defined('DEBUG_MODE') ? (DEBUG_MODE ? 'development' : 'production') : 'unknown'
            ]
        ];
    }
    
    private function addCheck($name, $status, $message, $details = null) {
        $this->checks[] = [
            'name' => $name,
            'status' => $status, // 'success', 'warning', 'error'
            'message' => $message,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkPHPVersion() {
        $required = '7.4';
        $current = phpversion();
        
        if (version_compare($current, $required, '>=')) {
            $this->addCheck('PHP Version', 'success', "PHP {$current} (minimum {$required} required)");
        } else {
            $this->addCheck('PHP Version', 'error', "PHP {$current} (minimum {$required} required)", [
                'current' => $current,
                'required' => $required
            ]);
        }
    }
    
    private function checkDatabase() {
        try {
            $conn = getConnection();
            // Coba eksekusi query sederhana
            $stmt = $conn->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if ($result) {
                $this->addCheck('Database Connection', 'success', 'Connected successfully to database');
                
                // Cek apakah tabel utama ada
                $tables = ['answers', 'users', 'question_logs', 'api_keys'];
                $missingTables = [];
                
                foreach ($tables as $table) {
                    $stmt = $conn->query("SHOW TABLES LIKE '{$table}'");
                    if ($stmt->rowCount() == 0) {
                        $missingTables[] = $table;
                    }
                }
                
                if (count($missingTables) > 0) {
                    $this->addCheck('Database Tables', 'warning', 'Some required tables are missing: ' . implode(', ', $missingTables), [
                        'missing_tables' => $missingTables
                    ]);
                } else {
                    $this->addCheck('Database Tables', 'success', 'All required tables exist');
                }
            } else {
                $this->addCheck('Database Connection', 'error', 'Connection established but query failed');
            }
        } catch (Exception $e) {
            $this->addCheck('Database Connection', 'error', 'Database connection failed: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function checkRequiredExtensions() {
        $required = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];
        $missing = [];
        
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        
        if (count($missing) == 0) {
            $this->addCheck('Required Extensions', 'success', 'All required PHP extensions are loaded');
        } else {
            $this->addCheck('Required Extensions', 'error', 'Missing PHP extensions: ' . implode(', ', $missing), [
                'missing_extensions' => $missing
            ]);
        }
    }
    
    private function checkFilePermissions() {
        // Cek direktori penting
        $dirs = [
            'server/',
            'assets/',
            'logs/' => true, // opsional
            'database/'
        ];
        
        $issues = [];
        
        foreach ($dirs as $dir => $optional) {
            if (is_string($optional)) { // Jika bukan optional flag, tapi nama direktori
                $optional = false;
                $dir = $optional;
            }
            
            if (!is_dir(__DIR__ . '/../' . $dir)) {
                if (!$optional) {
                    $issues[] = "Directory missing: {$dir}";
                }
            } else {
                if (!is_writable(__DIR__ . '/../' . $dir) && $dir === 'logs/') {
                    $issues[] = "Directory not writable: {$dir}";
                }
            }
        }
        
        if (count($issues) == 0) {
            $this->addCheck('File Permissions', 'success', 'Directory permissions are correct');
        } else {
            $this->addCheck('File Permissions', 'warning', 'Some permission issues found: ' . implode(', ', $issues), [
                'issues' => $issues
            ]);
        }
    }
    
    private function checkAPIConnectivity() {
        // Cek ketersediaan layanan eksternal
        $apis = [
            'Google Fonts' => 'https://fonts.googleapis.com/css2?family=Poppins',
            'Font Awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
        ];
        
        $unreachable = [];
        
        foreach ($apis as $name => $url) {
            // Cek ketersediaan dengan cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode !== 200 || $error) {
                $unreachable[] = $name;
            }
        }
        
        if (count($unreachable) == 0) {
            $this->addCheck('External APIs', 'success', 'All external services are accessible');
        } else {
            $this->addCheck('External APIs', 'warning', 'Some external services are unreachable: ' . implode(', ', $unreachable), [
                'unreachable' => $unreachable
            ]);
        }
    }
    
    private function getOverallStatus() {
        $errors = 0;
        $warnings = 0;
        
        foreach ($this->checks as $check) {
            if ($check['status'] === 'error') {
                $errors++;
            } elseif ($check['status'] === 'warning') {
                $warnings++;
            }
        }
        
        if ($errors > 0) {
            return 'error';
        } elseif ($warnings > 0) {
            return 'warning';
        } else {
            return 'operational';
        }
    }
}

// Jalankan pengecekan
$checker = new StatusChecker();
$result = $checker->runAllChecks();

// Tambahkan informasi tambahan
$result['summary'] = [
    'total_checks' => count($result['checks']),
    'success_count' => count(array_filter($result['checks'], function($check) { return $check['status'] === 'success'; })),
    'warning_count' => count(array_filter($result['checks'], function($check) { return $check['status'] === 'warning'; })),
    'error_count' => count(array_filter($result['checks'], function($check) { return $check['status'] === 'error'; }))
];

echo json_encode($result, JSON_PRETTY_PRINT);
?>