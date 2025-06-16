<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$confession_id = (int)($data['confession_id'] ?? 0);
$reaction = $data['reaction'] ?? '';

$allowedReactions = ['love', 'funny', 'sad', 'bold'];

if (!$confession_id || !in_array($reaction, $allowedReactions, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid confession ID or reaction']);
    exit;
}

try {
    // Check if reaction row exists first
    $check = $pdo->prepare("SELECT 1 FROM reactions WHERE confession_id = ?");
    $check->execute([$confession_id]);
    if (!$check->fetch()) {
        // No reaction row exists, create one
        $insert = $pdo->prepare("INSERT INTO reactions (confession_id, love, funny, sad, bold) VALUES (?, 0, 0, 0, 0)");
        $insert->execute([$confession_id]);
    }

    // Safely update reaction count - since column name cannot be parameterized, we validated it above
    $stmt = $pdo->prepare("UPDATE reactions SET $reaction = $reaction + 1 WHERE confession_id = ?");
    $stmt->execute([$confession_id]);

    // Fetch updated counts
    $stmt2 = $pdo->prepare("SELECT love, funny, sad, bold FROM reactions WHERE confession_id = ?");
    $stmt2->execute([$confession_id]);
    $reactions = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($reactions) {
        echo json_encode(['success' => true, 'reactions' => array_map('intval', $reactions)]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Reactions not found for confession']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update reaction', 'details' => $e->getMessage()]);
}
