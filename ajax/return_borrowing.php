<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid borrowing ID');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    // Get borrowing details
    $stmt = $conn->prepare('SELECT * FROM borrowings WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $borrowing = $result->fetchArray(SQLITE3_ASSOC);

    if (!$borrowing) {
        throw new Exception('Peminjaman tidak ditemukan');
    }

    if ($borrowing['status'] !== 'aktif') {
        throw new Exception('Peminjaman sudah dikembalikan');
    }

    // Update borrowing status
    $stmt = $conn->prepare("UPDATE borrowings SET 
        status = 'selesai',
        actual_return_date = :return_date
        WHERE id = :id");

    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':return_date', date('Y-m-d'), SQLITE3_TEXT);
    $stmt->execute();

    // Update book stock
    $stmt = $conn->prepare('UPDATE books SET stock = stock + 1 WHERE id = :book_id');
    $stmt->bindValue(':book_id', $borrowing['book_id'], SQLITE3_INTEGER);
    $stmt->execute();

    // Calculate fine if late
    $fine = 0;
    if (strtotime($borrowing['return_date']) < strtotime('today')) {
        $days_late = floor((strtotime('today') - strtotime($borrowing['return_date'])) / (60 * 60 * 24));
        $fine = $days_late * 1000; // Rp 1.000 per hari
        
        // Update fine in database
        $stmt = $conn->prepare('UPDATE borrowings SET fine = :fine WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':fine', $fine, SQLITE3_FLOAT);
        $stmt->execute();
    }

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil dikembalikan' . ($fine > 0 ? ". Denda: Rp " . number_format($fine, 0, ',', '.') : ''),
        'fine' => $fine
    ]);

} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 