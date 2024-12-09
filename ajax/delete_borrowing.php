<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid borrowing ID');
    }

    // Get borrowing details
    $stmt = $conn->prepare('SELECT * FROM borrowings WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $borrowing = $result->fetchArray(SQLITE3_ASSOC);

    if (!$borrowing) {
        throw new Exception('Peminjaman tidak ditemukan');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    // If borrowing is active, return the book to stock
    if ($borrowing['status'] === 'aktif') {
        $stmt = $conn->prepare('UPDATE books SET stock = stock + 1 WHERE id = :book_id');
        $stmt->bindValue(':book_id', $borrowing['book_id'], SQLITE3_INTEGER);
        $stmt->execute();
    }

    // Delete borrowing record
    $stmt = $conn->prepare('DELETE FROM borrowings WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Data peminjaman berhasil dihapus'
    ]);

} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 