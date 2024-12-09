<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $title = SQLite3::escapeString($_POST['title']);
    $author = SQLite3::escapeString($_POST['author']);
    $publisher = SQLite3::escapeString($_POST['publisher']);
    $year = (int)$_POST['year'];
    $stock = (int)$_POST['stock'];
    $shelf = SQLite3::escapeString($_POST['shelf']);

    $stmt = $conn->prepare("INSERT INTO books (title, author, publisher, year, stock, shelf) 
                           VALUES (:title, :author, :publisher, :year, :stock, :shelf)");
    
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':author', $author, SQLITE3_TEXT);
    $stmt->bindValue(':publisher', $publisher, SQLITE3_TEXT);
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':stock', $stock, SQLITE3_INTEGER);
    $stmt->bindValue(':shelf', $shelf, SQLITE3_TEXT);
    
    $result = $stmt->execute();

    echo json_encode([
        'success' => true,
        'book_id' => $conn->lastInsertRowID()
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 