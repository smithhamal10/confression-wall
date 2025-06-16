<?php
require 'db.php';
try {
    $stmt = $pdo->query("SELECT 1");
    echo json_encode(["success" => true, "message" => "Database connected!"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
