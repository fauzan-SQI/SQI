<?php
// api_test.php - Halaman untuk menguji endpoint API

require_once 'server/bootstrap.php';

function testApiEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && $method !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Test beberapa endpoint API
$tests = [
    'get_answers' => [
        'url' => 'http://localhost/SQI/ScienceQuranIntegration/server/getAnswer.php?question=test',
        'method' => 'GET'
    ],
    'get_daily_fact' => [
        'url' => 'http://localhost/SQI/ScienceQuranIntegration/server/api/daily_fact.php',
        'method' => 'GET'
    ],
    'get_quran_api' => [
        'url' => 'http://localhost/SQI/ScienceQuranIntegration/server/quran_api.php',
        'method' => 'GET'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test - Science-Qur'an Integration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #001f3f;
            text-align: center;
        }
        .test-result {
            margin: 15px 0;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>API Test - Science-Qur'an Integration</h1>
        <p>Halaman ini digunakan untuk menguji endpoint API SQI.</p>
        
        <?php foreach ($tests as $testName => $testConfig): ?>
            <div class="test-result info">
                <h3>Mengujicoba: <?php echo htmlspecialchars($testName); ?></h3>
                <p><strong>URL:</strong> <?php echo htmlspecialchars($testConfig['url']); ?></p>
                <p><strong>Metode:</strong> <?php echo htmlspecialchars($testConfig['method']); ?></p>
                
                <?php
                $result = testApiEndpoint($testConfig['url'], $testConfig['method']);
                
                if ($result['error']) {
                    echo '<div class="test-result error">';
                    echo '<p><strong>Error:</strong> ' . htmlspecialchars($result['error']) . '</p>';
                    echo '</div>';
                } else {
                    echo '<div class="test-result ' . ($result['http_code'] === 200 ? 'success' : 'error') . '">';
                    echo '<p><strong>HTTP Code:</strong> ' . $result['http_code'] . '</p>';
                    echo '<p><strong>Response:</strong></p>';
                    echo '<pre>' . htmlspecialchars($result['response']) . '</pre>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php endforeach; ?>
        
        <h2>Endpoint Admin API</h2>
        <p>Catatan: Endpoint admin API memerlukan otentikasi admin. Mereka akan diuji setelah login.</p>
        
        <ul>
            <li>GET /admin/api/videos.php</li>
            <li>POST /admin/api/videos.php</li>
            <li>PUT /admin/api/videos.php?id=X</li>
            <li>DELETE /admin/api/videos.php?id=X</li>
            <li>GET /admin/api/api_keys.php</li>
            <li>GET /admin/api/instructions.php</li>
            <li>GET /admin/api/question_logs.php</li>
        </ul>
    </div>
</body>
</html>
