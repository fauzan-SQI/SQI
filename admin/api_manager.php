<?php
// api_manager.php - API key management for admin panel

require_once '../server/bootstrap.php';
require_once 'admin_auth.php';

class ApiManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function getAllApiKeys() {
        try {
            $stmt = $this->conn->query("SELECT id, api_name, is_active, created_at, updated_at FROM api_keys ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting API keys: " . $e->getMessage());
            return [];
        }
    }
    
    public function getApiKey($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id, api_name, api_key, is_active, created_at, updated_at FROM api_keys WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting API key: " . $e->getMessage());
            return null;
        }
    }
    
    public function addApiKey($apiName, $apiKey, $isActive = true) {
        try {
            $hashedKey = password_hash($apiKey, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO api_keys (api_name, api_key, is_active) VALUES (?, ?, ?)");
            $result = $stmt->execute([$apiName, $hashedKey, $isActive]);
            
            return $result ? $this->conn->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error adding API key: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateApiKey($id, $apiName, $apiKey = null, $isActive) {
        try {
            if ($apiKey) {
                // Update with new API key
                $hashedKey = password_hash($apiKey, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE api_keys SET api_name = ?, api_key = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                return $stmt->execute([$apiName, $hashedKey, $isActive, $id]);
            } else {
                // Update without changing API key
                $stmt = $this->conn->prepare("UPDATE api_keys SET api_name = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                return $stmt->execute([$apiName, $isActive, $id]);
            }
        } catch (Exception $e) {
            error_log("Error updating API key: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteApiKey($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM api_keys WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error deleting API key: " . $e->getMessage());
            return false;
        }
    }
    
    public function testApiKey($apiKey) {
        // In a real implementation, you would test the API key against the provider
        // For now, just return true if the key looks valid
        return strlen($apiKey) >= 30; // Simple validation
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Example usage:
// $apiManager = new ApiManager();
// $allKeys = $apiManager->getAllApiKeys();
// $success = $apiManager->addApiKey("OpenAI API", "sk-..."); 
?>
