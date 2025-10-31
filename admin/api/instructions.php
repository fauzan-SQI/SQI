<?php
// admin/api/instructions.php - API endpoint for managing system instructions

require_once '../admin_check.php';
require_once '../../server/config.php';

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
        $active = $_GET['active'] ?? null;
        
        if ($active) {
            // Get active instruction
            $stmt = $conn->prepare("SELECT id, instruction_text, created_at, updated_at FROM system_instructions WHERE is_active = 1");
            $stmt->execute();
            $instruction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($instruction) {
                echo json_encode($instruction);
            } else {
                echo json_encode(null);
            }
        } else {
            // Get all instructions
            $stmt = $conn->query("SELECT id, instruction_text, is_active, created_at, updated_at FROM system_instructions ORDER BY created_at DESC");
            $instructions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($instructions);
        }
        break;
        
    case 'POST':
        // Create new instruction
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['instruction_text'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Instruction text is required']);
            break;
        }
        
        // If making this instruction active, deactivate others first
        if ($input['is_active'] ?? false) {
            $conn->prepare("UPDATE system_instructions SET is_active = 0 WHERE is_active = 1")->execute();
        }
        
        $stmt = $conn->prepare("INSERT INTO system_instructions (instruction_text, is_active) VALUES (?, ?)");
        $result = $stmt->execute([
            $input['instruction_text'], 
            $input['is_active'] ?? 0
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create instruction']);
        }
        break;
        
    case 'PUT':
        // Update instruction
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Instruction ID is required']);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $fields = [];
        $params = [];
        
        if (isset($input['instruction_text'])) {
            $fields[] = "instruction_text = ?";
            $params[] = $input['instruction_text'];
        }
        if (isset($input['is_active'])) {
            // If making this instruction active, deactivate others first
            if ($input['is_active']) {
                $conn->prepare("UPDATE system_instructions SET is_active = 0 WHERE is_active = 1")->execute();
            }
            $fields[] = "is_active = ?";
            $params[] = $input['is_active'];
        }
        
        if (count($fields) === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            break;
        }
        
        $params[] = $id; // for WHERE clause
        $stmt = $conn->prepare("UPDATE system_instructions SET " . implode(', ', $fields) . " WHERE id = ?");
        $result = $stmt->execute($params);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update instruction']);
        }
        break;
        
    case 'DELETE':
        // Delete instruction
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Instruction ID is required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM system_instructions WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete instruction']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>