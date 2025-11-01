<?php
// admin/api/question_logs.php - API endpoint for managing question logs

require_once '../admin_check.php';
require_once '../../server/bootstrap.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $recent = $_GET['recent'] ?? null;
        $limit = $_GET['limit'] ?? 50;
        $stats = $_GET['stats'] ?? null;
        
        if ($stats) {
            // Get statistics
            $totalStmt = $conn->query("SELECT COUNT(*) as total FROM question_logs");
            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $todayStmt = $conn->query("SELECT COUNT(*) as today FROM question_logs WHERE DATE(created_at) = CURDATE()");
            $today = $todayStmt->fetch(PDO::FETCH_ASSOC)['today'];
            
            echo json_encode(['total' => $total, 'today' => $today]);
        } elseif ($recent) {
            // Get recent question logs
            $stmt = $conn->prepare("SELECT * FROM question_logs ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($logs);
        } else {
            // Get all question logs
            $stmt = $conn->query("SELECT * FROM question_logs ORDER BY created_at DESC");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($logs);
        }
        break;
        
    case 'DELETE':
        // Delete question log
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Log ID is required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM question_logs WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete log']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
