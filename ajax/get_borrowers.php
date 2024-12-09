<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $result = $conn->query('SELECT * FROM borrowers ORDER BY created_at DESC');
    $borrowers = [];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $borrowers[] = $row;
    }
    
    echo json_encode($borrowers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 