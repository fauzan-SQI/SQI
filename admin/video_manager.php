<?php
// video_manager.php - Video database management for admin panel

require_once '../server/bootstrap.php';
require_once '../server/video_config.php'; // Include video validation functions
require_once 'admin_auth.php';

class VideoManager {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function getAllVideos($limit = 50, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("SELECT id, keyword, title, youtube_url, description, created_at, updated_at FROM videos ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting videos: " . $e->getMessage());
            return [];
        }
    }
    
    public function getVideo($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id, keyword, title, youtube_url, description, created_at, updated_at FROM videos WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting video: " . $e->getMessage());
            return null;
        }
    }
    
    public function addVideo($keyword, $title, $youtubeUrl, $description = '') {
        try {
            // Validasi URL YouTube sebelum disimpan
            $youtubeUrl = $this->validateAndFormatUrl($youtubeUrl);
            
            $stmt = $this->conn->prepare("INSERT INTO videos (keyword, title, youtube_url, description) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$keyword, $title, $youtubeUrl, $description]);
            
            return $result ? $this->conn->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error adding video: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateVideo($id, $keyword, $title, $youtubeUrl, $description) {
        try {
            // Validasi URL YouTube sebelum disimpan
            $youtubeUrl = $this->validateAndFormatUrl($youtubeUrl);
            
            $stmt = $this->conn->prepare("UPDATE videos SET keyword = ?, title = ?, youtube_url = ?, description = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$keyword, $title, $youtubeUrl, $description, $id]);
        } catch (Exception $e) {
            error_log("Error updating video: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteVideo($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM videos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error deleting video: " . $e->getMessage());
            return false;
        }
    }
    
    public function searchVideos($keyword) {
        try {
            $searchTerm = '%' . $keyword . '%';
            $stmt = $this->conn->prepare("SELECT id, keyword, title, youtube_url FROM videos WHERE keyword LIKE ? OR title LIKE ? ORDER BY created_at DESC");
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error searching videos: " . $e->getMessage());
            return [];
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    private function validateAndFormatUrl($url) {
        if (empty($url)) {
            // Jika URL kosong, kembalikan video default
            return DEFAULT_GENERAL_VIDEO;
        }
        
        // Validasi URL dan konversi ke format embed jika perlu
        if (isValidYouTubeUrl($url)) {
            return convertToEmbedUrl($url);
        } else {
            // Jika tidak valid, kembalikan video fallback
            return getFallbackVideo($url);
        }
    }
}

// Example usage:
// $videoManager = new VideoManager();
// $allVideos = $videoManager->getAllVideos();
// $newId = $videoManager->addVideo("penciptaan manusia", "Proses Penciptaan Manusia", "https://youtube.com/embed/...");
?>
