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

    // Check for active borrowings
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM borrowings WHERE borrower_id = :id AND status = "aktif"');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['count'] > 0) {
        throw new Exception('Peminjam tidak dapat dihapus karena masih memiliki peminjaman aktif');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    $stmt = $conn->prepare('DELETE FROM borrowers WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal menghapus peminjam: ' . $conn->lastErrorMsg());
    }

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Peminjam berhasil dihapus'
    ]);

} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 