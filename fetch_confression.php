<?php
header("Content-Type: application/json");
require 'db.php';

try {
    $stmt = $pdo->query("
        SELECT c.id, c.message, c.mood, c.timestamp, 
               r.love, r.funny, r.sad, r.bold
        FROM confessions c
        LEFT JOIN reactions r ON c.id = r.confession_id
        ORDER BY c.timestamp DESC
        LIMIT 100
    ");
    $confessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($confessions);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch confessions"]);
}
