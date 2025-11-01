<?php
// admin/api/api_keys.php - API endpoint for managing API keys

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
        // Get all API keys
        $stmt = $conn->query("SELECT id, is_active, created_at, updated_at FROM api_keys ORDER BY created_at DESC");
        $api_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($api_keys);
        break;
        
    case 'POST':
        // Create new API key
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['api_key'])) {
            http_response_code(400);
            echo json_encode(['error' => 'API key is required']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO api_keys (api_key, is_active) VALUES (?, ?)");
        $result = $stmt->execute([
            $input['api_key'], 
            $input['is_active'] ?? 1
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create API key']);
        }
        break;
        
    case 'PUT':
        // Update API key
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'API key ID is required']);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $fields = [];
        $params = [];
        
        if (isset($input['api_key'])) {
            $fields[] = "api_key = ?";
            $params[] = $input['api_key'];
        }
        if (isset($input['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = $input['is_active'];
        }
        
        if (count($fields) === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            break;
        }
        
        $params[] = $id; // for WHERE clause
        $stmt = $conn->prepare("UPDATE api_keys SET " . implode(', ', $fields) . " WHERE id = ?");
        $result = $stmt->execute($params);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update API key']);
        }
        break;
        
    case 'DELETE':
        // Delete API key
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'API key ID is required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete API key']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
