<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    // Validate input
    if (empty($_POST['name']) || empty($_POST['id_number'])) {
        throw new Exception('Nama dan Nomor Identitas harus diisi');
    }

    // Check for duplicate ID number
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM borrowers WHERE id_number = :id_number');
    $stmt->bindValue(':id_number', $_POST['id_number'], SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['count'] > 0) {
        throw new Exception('Nomor Identitas sudah terdaftar');
    }

    // Begin transaction
    $conn->exec('BEGIN TRANSACTION');

    $stmt = $conn->prepare("INSERT INTO borrowers (name, id_number, phone, address) 
                           VALUES (:name, :id_number, :phone, :address)");

    $stmt->bindValue(':name', $_POST['name'], SQLITE3_TEXT);
    $stmt->bindValue(':id_number', $_POST['id_number'], SQLITE3_TEXT);
    $stmt->bindValue(':phone', $_POST['phone'], SQLITE3_TEXT);
    $stmt->bindValue(':address', $_POST['address'], SQLITE3_TEXT);

    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Gagal menambahkan peminjam: ' . $conn->lastErrorMsg());
    }

    // Commit transaction
    $conn->exec('COMMIT');

    echo json_encode([
        'success' => true,
        'borrower_id' => $conn->lastInsertRowID(),
        'message' => 'Peminjam berhasil ditambahkan'
    ]);

} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 