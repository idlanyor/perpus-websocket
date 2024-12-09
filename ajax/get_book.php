<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid book ID');
    }

    $stmt = $conn->prepare('SELECT * FROM books WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $book = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$book) {
        throw new Exception('Book not found');
    }

    echo json_encode($book);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 