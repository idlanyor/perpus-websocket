<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    // Debug: Check connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Debug: Print query
    $query = 'SELECT * FROM books ORDER BY created_at DESC';
    error_log("Executing query: " . $query);

    $result = $conn->query($query);
    
    // Debug: Check query result
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->lastErrorMsg());
    }

    $books = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $books[] = $row;
    }
    
    // Debug: Print result count
    error_log("Found " . count($books) . " books");
    
    echo json_encode($books);
} catch (Exception $e) {
    error_log("Error in get_books.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 