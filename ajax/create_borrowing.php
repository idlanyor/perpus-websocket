<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    // Validate input
    if (empty($_POST['book_id']) || empty($_POST['borrower_id']) || empty($_POST['return_date'])) {
        throw new Exception('Semua field harus diisi');
    }

    $book_id = filter_var($_POST['book_id'], FILTER_VALIDATE_INT);
    $borrower_id = filter_var($_POST['borrower_id'], FILTER_VALIDATE_INT);
    $return_date = $_POST['return_date'];

    // Validate return date
    $return_timestamp = strtotime($return_date);
    if ($return_timestamp < strtotime('today')) {
        throw new Exception('Tanggal kembali tidak boleh kurang dari hari ini');
    }

    // Check book availability
    $stmt = $conn->prepare('SELECT stock FROM books WHERE id = :book_id');
    $stmt->bindValue(':book_id', $book_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $book = $result->fetchArray(SQLITE3_ASSOC);

    if (!$book || $book['stock'] <= 0) {
        throw new Exception('Buku tidak tersedia untuk dipinjam');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    // Create borrowing record
    $stmt = $conn->prepare("INSERT INTO borrowings 
        (book_id, borrower_id, borrow_date, return_date, status) 
        VALUES (:book_id, :borrower_id, :borrow_date, :return_date, 'aktif')");

    $stmt->bindValue(':book_id', $book_id, SQLITE3_INTEGER);
    $stmt->bindValue(':borrower_id', $borrower_id, SQLITE3_INTEGER);
    $stmt->bindValue(':borrow_date', date('Y-m-d'), SQLITE3_TEXT);
    $stmt->bindValue(':return_date', $return_date, SQLITE3_TEXT);
    
    $result = $stmt->execute();

    // Update book stock
    $stmt = $conn->prepare('UPDATE books SET stock = stock - 1 WHERE id = :book_id');
    $stmt->bindValue(':book_id', $book_id, SQLITE3_INTEGER);
    $stmt->execute();

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'borrowing_id' => $conn->lastInsertRowID(),
        'message' => 'Peminjaman berhasil ditambahkan'
    ]);

} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}