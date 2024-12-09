<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    // Validate input
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid book ID');
    }

    // Get current book data for stock validation
    $stmt = $conn->prepare('SELECT stock FROM books WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $currentBook = $result->fetchArray(SQLITE3_ASSOC);

    if (!$currentBook) {
        throw new Exception('Book not found');
    }

    // Get active borrowings count
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM borrowings WHERE book_id = :id AND status = "aktif"');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $borrowings = $result->fetchArray(SQLITE3_ASSOC);
    $activeBorrowings = $borrowings['count'];

    // Validate new stock
    $newStock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    if ($newStock < $activeBorrowings) {
        throw new Exception('Stok tidak boleh kurang dari jumlah peminjaman aktif');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    $stmt = $conn->prepare("UPDATE books SET 
        title = :title,
        author = :author,
        publisher = :publisher,
        year = :year,
        stock = :stock,
        shelf = :shelf
        WHERE id = :id");

    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':title', $_POST['title'], SQLITE3_TEXT);
    $stmt->bindValue(':author', $_POST['author'], SQLITE3_TEXT);
    $stmt->bindValue(':publisher', $_POST['publisher'], SQLITE3_TEXT);
    $stmt->bindValue(':year', $_POST['year'], SQLITE3_INTEGER);
    $stmt->bindValue(':stock', $newStock, SQLITE3_INTEGER);
    $stmt->bindValue(':shelf', $_POST['shelf'], SQLITE3_TEXT);

    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal mengupdate buku: ' . $conn->lastErrorMsg());
    }

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil diupdate'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->exec('ROLLBACK');
    
    error_log("Error updating book: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 