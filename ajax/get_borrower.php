<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid borrower ID');
    }

    $stmt = $conn->prepare('SELECT * FROM borrowers WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $borrower = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$borrower) {
        throw new Exception('Borrower not found');
    }

    echo json_encode($borrower);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 