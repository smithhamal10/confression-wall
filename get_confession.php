<?php
header('Content-Type: application/json');
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Confession ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.message, c.mood, c.created_at,
               COALESCE(r.love, 0) AS love,
               COALESCE(r.funny, 0) AS funny,
               COALESCE(r.sad, 0) AS sad,
               COALESCE(r.bold, 0) AS bold
        FROM confessions c
        LEFT JOIN reactions r ON c.id = r.confession_id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);

    $confession = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$confession) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Confession not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'confession' => [
            'id' => (int)$confession['id'],
            'message' => $confession['message'],
            'mood' => $confession['mood'],
            'timestamp' => $confession['created_at'],
            'reactions' => [
                'love' => (int)$confession['love'],
                'funny' => (int)$confession['funny'],
                'sad' => (int)$confession['sad'],
                'bold' => (int)$confession['bold'],
            ]
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch confession', 'details' => $e->getMessage()]);
}
