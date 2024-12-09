<?php
class Database {
    private $db;

    public function __construct() {
        try {
            $this->db = new SQLite3(__DIR__ . '/../database/library.db');
            $this->createTables();
        } catch (Exception $e) {
            die("Error connecting to database: " . $e->getMessage());
        }
    }

    private function createTables() {
        // Tabel Buku
        $this->db->exec("CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            publisher TEXT NOT NULL,
            year INTEGER NOT NULL,
            stock INTEGER DEFAULT 0,
            shelf TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Tabel Peminjam
        $this->db->exec("CREATE TABLE IF NOT EXISTS borrowers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            id_number TEXT UNIQUE NOT NULL,
            phone TEXT,
            address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Tabel Peminjaman
        $this->db->exec("CREATE TABLE IF NOT EXISTS borrowings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            book_id INTEGER,
            borrower_id INTEGER,
            borrow_date DATE NOT NULL,
            return_date DATE NOT NULL,
            actual_return_date DATE,
            status TEXT DEFAULT 'aktif',
            fine DECIMAL(10,2) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id),
            FOREIGN KEY (borrower_id) REFERENCES borrowers(id)
        )");
    }

    public function getConnection() {
        return $this->db;
    }
} 