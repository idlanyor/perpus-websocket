<?php
session_start();
require_once __DIR__ . '/../config/database.php';
$db = new Database();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perpustakaan</title>
    <script src="https://kit.fontawesome.com/3c2f0f4372.js" crossorigin="anonymous"></script>
    <script src="assets/js/websocket-client.js"></script>
</head>

<body class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <div class="bg-gray-800 p-5 flex items-center justify-between fixed top-0 w-full">
        <div class="text-white">
            <span>Aplikasi Peminjaman Buku</span>
        </div>
        <div class="flex gap-4">
            <a href="index.php" class="text-white hover:text-gray-300">Peminjaman</a>
            <a href="books.php" class="text-white hover:text-gray-300">Manajemen Buku</a>
            <a href="borrowers.php" class="text-white hover:text-gray-300">Data Peminjam</a>
        </div>
    </div>
    <div class="container mx-auto p-5 mt-16">