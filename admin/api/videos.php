<?php
// admin/api/videos.php - API endpoint for managing videos

require_once '../admin_check.php';
require_once '../../server/bootstrap.php';
require_once '../../server/video_config.php'; // Include video validation functions

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
        $id = $_GET['id'] ?? null;
        $count = $_GET['count'] ?? null;
        
        if ($id) {
            // Get specific video by ID
            $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
            $stmt->execute([$id]);
            $video = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($video) {
                echo json_encode($video);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Video not found']);
            }
        } elseif ($count) {
            // Get count of videos
            $stmt = $conn->query("SELECT COUNT(*) as count FROM videos");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($count);
        } else {
            // Get all videos
            $stmt = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
            $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($videos);
        }
        break;
        
    case 'POST':
        // Create new video
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['keyword']) || !isset($input['title']) || !isset($input['youtube_url'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            break;
        }
        
        // Validasi dan format URL YouTube
        $input['youtube_url'] = isValidYouTubeUrl($input['youtube_url']) ? convertToEmbedUrl($input['youtube_url']) : getFallbackVideo($input['youtube_url']);
        
        $stmt = $conn->prepare("INSERT INTO videos (keyword, title, youtube_url, description) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            $input['keyword'], 
            $input['title'], 
            $input['youtube_url'], 
            $input['description'] ?? null
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create video']);
        }
        break;
        
    case 'PUT':
        // Update video
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Video ID is required']);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validasi dan format URL YouTube jika disediakan
        if (isset($input['youtube_url'])) {
            $input['youtube_url'] = isValidYouTubeUrl($input['youtube_url']) ? convertToEmbedUrl($input['youtube_url']) : getFallbackVideo($input['youtube_url'], 'general');
        }
        
        $stmt = $conn->prepare("UPDATE videos SET keyword = ?, title = ?, youtube_url = ?, description = ? WHERE id = ?");
        $result = $stmt->execute([
            $input['keyword'] ?? null,
            $input['title'] ?? null,
            $input['youtube_url'] ?? null,
            $input['description'] ?? null,
            $id
        ]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update video']);
        }
        break;
        
    case 'DELETE':
        // Delete video
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Video ID is required']);
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete video']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
