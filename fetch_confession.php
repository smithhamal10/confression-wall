<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // Join confessions with reactions to get reaction counts along with each confession
    $stmt = $pdo->prepare("
        SELECT c.id, c.message, c.mood, c.created_at, 
               COALESCE(r.love, 0) AS love, 
               COALESCE(r.funny, 0) AS funny, 
               COALESCE(r.sad, 0) AS sad, 
               COALESCE(r.bold, 0) AS bold
        FROM confessions c
        LEFT JOIN reactions r ON c.id = r.confession_id
        ORDER BY c.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();

    $confessions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $confessions[] = [
            'id' => (int)$row['id'],
            'message' => $row['message'],
            'mood' => $row['mood'],
            'timestamp' => $row['created_at'],
            'reactions' => [
                'love' => (int)$row['love'],
                'funny' => (int)$row['funny'],
                'sad' => (int)$row['sad'],
                'bold' => (int)$row['bold'],
            ],
        ];
    }

    echo json_encode(['success' => true, 'confessions' => $confessions]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch confessions', 'details' => $e->getMessage()]);
}
