<?php
// instruction_manager.php - System instruction management for admin panel

require_once '../server/config.php';
require_once 'admin_auth.php';

class InstructionManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function getAllInstructions() {
        try {
            $stmt = $this->conn->query("SELECT id, instruction_name, instruction_text, is_active, created_at, updated_at FROM system_instructions ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting instructions: " . $e->getMessage());
            return [];
        }
    }
    
    public function getActiveInstruction() {
        try {
            $stmt = $this->conn->query("SELECT id, instruction_name, instruction_text, is_active, created_at, updated_at FROM system_instructions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting active instruction: " . $e->getMessage());
            return null;
        }
    }
    
    public function getInstruction($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id, instruction_name, instruction_text, is_active, created_at, updated_at FROM system_instructions WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting instruction: " . $e->getMessage());
            return null;
        }
    }
    
    public function addInstruction($name, $text, $isActive = false) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO system_instructions (instruction_name, instruction_text, is_active) VALUES (?, ?, ?)");
            $result = $stmt->execute([$name, $text, $isActive]);
            
            return $result ? $this->conn->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error adding instruction: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateInstruction($id, $name, $text, $isActive) {
        try {
            $stmt = $this->conn->prepare("UPDATE system_instructions SET instruction_name = ?, instruction_text = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$name, $text, $isActive, $id]);
        } catch (Exception $e) {
            error_log("Error updating instruction: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteInstruction($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM system_instructions WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error deleting instruction: " . $e->getMessage());
            return false;
        }
    }
    
    public function setActiveInstruction($id) {
        try {
            // First, deactivate all instructions
            $this->conn->query("UPDATE system_instructions SET is_active = 0");
            
            // Then activate the specified one
            $stmt = $this->conn->prepare("UPDATE system_instructions SET is_active = 1 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error setting active instruction: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Example usage:
// $instManager = new InstructionManager();
// $activeInst = $instManager->getActiveInstruction();
// $success = $instManager->updateInstruction(1, "New AI Instruction", "You are an expert...", true);
?>