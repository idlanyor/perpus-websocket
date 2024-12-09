<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    // Get ID from URL
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid book ID');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    // Check if book has active borrowings
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM borrowings WHERE book_id = :id AND status = "aktif"');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['count'] > 0) {
        throw new Exception('Buku tidak dapat dihapus karena masih dalam peminjaman aktif');
    }

    // Delete the book
    $stmt = $conn->prepare('DELETE FROM books WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal menghapus buku: ' . $conn->lastErrorMsg());
    }

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil dihapus'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->exec('ROLLBACK');
    
    error_log("Error deleting book: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 