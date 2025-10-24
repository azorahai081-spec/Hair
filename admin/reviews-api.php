<?php
require_once 'includes/auth-check.php'; // $csrf_token and new functions
require_once '../db-config.php';

header('Content-Type: application/json');

function send_json($data) {
    echo json_encode($data);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_REQUEST['action'] ?? null;

    if ($method === 'GET') {
        if ($action === 'get_single' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $review = $stmt->fetch();
            send_json($review);
        } else {
            // Fetch all reviews for the admin panel (regardless of status)
            $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id DESC");
            $reviews = $stmt->fetchAll();
            send_json($reviews);
        }
    } elseif ($method === 'POST') {
        
        // --- CSRF Token Validation ---
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
             http_response_code(403);
             // --- THIS WAS THE BUG: 'status's' is now 'status' ---
             send_json(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        }
        // --- END FIX ---

        switch ($action) {
            case 'create':
                if (!can_manage_reviews()) {
                     http_response_code(403);
                     send_json(['status' => 'error', 'message' => 'Permission denied.']);
                }
                $stmt = $pdo->prepare("INSERT INTO reviews (name, date, rating, review_text, image_initials, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'], $_POST['date'], $_POST['rating'], 
                    $_POST['review_text'], $_POST['image_initials'], $_POST['status']
                ]);
                send_json(['status' => 'success', 'message' => 'Review added.']);
                break;

            case 'update':
                if (!can_manage_reviews()) {
                     http_response_code(403);
                     send_json(['status' => 'error', 'message' => 'Permission denied.']);
                }
                $stmt = $pdo->prepare("UPDATE reviews SET name = ?, date = ?, rating = ?, review_text = ?, image_initials = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'], $_POST['date'], $_POST['rating'], 
                    $_POST['review_text'], $_POST['image_initials'], $_POST['status'], 
                    $_POST['id']
                ]);
                send_json(['status' => 'success', 'message' => 'Review updated.']);
                break;

            case 'delete':
                if (!can_delete_reviews()) {
                     http_response_code(403);
                     send_json(['status' => 'error', 'message' => 'Permission denied.']);
                }
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                send_json(['status' => 'success', 'message' => 'Review deleted.']);
                break;
            
            case 'toggle_feature':
                if (!can_manage_reviews()) {
                     http_response_code(403);
                     send_json(['status' => 'error', 'message' => 'Permission denied.']);
                }
                $stmt = $pdo->prepare("SELECT is_featured FROM reviews WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $is_featured = $stmt->fetchColumn();
                
                $new_state = $is_featured ? 0 : 1;
                
                $update_stmt = $pdo->prepare("UPDATE reviews SET is_featured = ? WHERE id = ?");
                $update_stmt->execute([$new_state, $_POST['id']]);
                send_json(['status' => 'success', 'message' => 'Feature status toggled.']);
                break;

            default:
                http_response_code(400);
                send_json(['status' => 'error', 'message' => 'Invalid action.']);
                break;
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    send_json(['status' => 'error', 'message' => 'Database operation failed: ' . $e->getMessage()]);
}
?>












