<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

try {
    $query = "SELECT 
                borrowings.*,
                books.title as book_title,
                books.author as book_author,
                borrowers.name as borrower_name,
                borrowers.id_number as borrower_id_number
              FROM borrowings
              JOIN books ON borrowings.book_id = books.id
              JOIN borrowers ON borrowings.borrower_id = borrowers.id
              ORDER BY borrowings.created_at DESC";

    $result = $conn->query($query);
    $borrowings = [];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Calculate fine if needed
        if ($row['status'] === 'aktif' && strtotime($row['return_date']) < strtotime('today')) {
            $days_late = floor((strtotime('today') - strtotime($row['return_date'])) / (60 * 60 * 24));
            $row['fine'] = $days_late * 1000; 
        } else {
            $row['fine'] = 0;
        }
        $borrowings[] = $row;
    }
    
    echo json_encode($borrowings);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 