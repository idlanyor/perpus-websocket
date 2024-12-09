<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    // Validate input
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid borrower ID');
    }

    // Check for duplicate ID number
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM borrowers WHERE id_number = :id_number AND id != :id');
    $stmt->bindValue(':id_number', $_POST['id_number'], SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['count'] > 0) {
        throw new Exception('Nomor Identitas sudah terdaftar');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    $stmt = $conn->prepare("UPDATE borrowers SET 
        name = :name,
        id_number = :id_number,
        phone = :phone,
        address = :address
        WHERE id = :id");

    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $_POST['name'], SQLITE3_TEXT);
    $stmt->bindValue(':id_number', $_POST['id_number'], SQLITE3_TEXT);
    $stmt->bindValue(':phone', $_POST['phone'], SQLITE3_TEXT);
    $stmt->bindValue(':address', $_POST['address'], SQLITE3_TEXT);

    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal mengupdate peminjam: ' . $conn->lastErrorMsg());
    }

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'message' => 'Data peminjam berhasil diupdate'
    ]);

} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 