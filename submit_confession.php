<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$message = trim($data['message'] ?? '');
$mood = trim($data['mood'] ?? '');

if (!$message) {
    http_response_code(400);
    echo json_encode(["error" => "Message is required"]);
    exit;
}

if (!$mood) {
    http_response_code(400);
    echo json_encode(["error" => "Mood is required"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO confessions (message, mood) VALUES (?, ?)");
    $stmt->execute([$message, $mood]);
    $confession_id = $pdo->lastInsertId();

    // Insert reactions row with default 0 for all reactions explicitly
    $stmt2 = $pdo->prepare("INSERT INTO reactions (confession_id, love, funny, sad, bold) VALUES (?, 0, 0, 0, 0)");
    $stmt2->execute([$confession_id]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "confession" => [
            "id" => (int)$confession_id,
            "message" => $message,
            "mood" => $mood,
            "timestamp" => date('Y-m-d H:i:s'),
            "reactions" => ["love" => 0, "funny" => 0, "sad" => 0, "bold" => 0]
        ]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "Failed to save confession", "details" => $e->getMessage()]);
}
