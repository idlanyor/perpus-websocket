<?php include 'includes/header.php'; ?>

<div class="bg-white p-5 rounded-lg shadow-md mb-5">
    <h2 class="text-xl font-bold mb-4">Tambah Peminjaman Baru</h2>
    <form id="borrowingForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <select name="book_id" id="bookSelect" class="p-2 border rounded-lg" required>
            <option value="">Pilih Buku...</option>
            <?php
            $query = "SELECT * FROM books WHERE stock > 0";
            $result = $db->getConnection()->query($query);
            while ($book = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<option value='{$book['id']}'>{$book['title']} - {$book['author']} (Stok: {$book['stock']})</option>";
            }
            ?>
        </select>
        
        <select name="borrower_id" id="borrowerSelect" class="p-2 border rounded-lg" required>
            <option value="">Pilih Peminjam...</option>
            <?php
            $query = "SELECT * FROM borrowers ORDER BY name ASC";
            $result = $db->getConnection()->query($query);
            while ($borrower = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<option value='{$borrower['id']}'>{$borrower['name']} ({$borrower['id_number']})</option>";
            }
            ?>
        </select>

        <input type="date" name="return_date" id="returnDate" 
            class="p-2 border rounded-lg" 
            min="<?php echo date('Y-m-d'); ?>" 
            required>

        <button type="submit" 
            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
            Tambah Peminjaman
        </button>
    </form>
</div>

<div class="bg-white p-5 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Peminjaman</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">Tanggal Pinjam</th>
                    <th class="py-2 px-4 text-left">Batas Kembali</th>
                    <th class="py-2 px-4 text-left">Judul Buku</th>
                    <th class="py-2 px-4 text-left">Peminjam</th>
                    <th class="py-2 px-4 text-left">Status</th>
                    <th class="py-2 px-4 text-left">Denda</th>
                    <th class="py-2 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody id="borrowingsList">
                <!-- Data will be loaded dynamically -->
            </tbody>
        </table>
    </div>
</div>

<script>
// Fungsi untuk memformat tanggal
function formatDate(dateString) {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const date = new Date(dateString);
    return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

// Load borrowings when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadBorrowings();
});

// Function to load and display borrowings
function loadBorrowings() {
    fetch('ajax/get_borrowings.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('borrowingsList');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="py-4 text-center">Tidak ada data peminjaman</td></tr>';
            } else {
                tbody.innerHTML = data.map(borrowing => `
                    <tr class="border-t">
                        <td class="py-2 px-4">${formatDate(borrowing.borrow_date)}</td>
                        <td class="py-2 px-4">${formatDate(borrowing.return_date)}</td>
                        <td class="py-2 px-4">${borrowing.book_title}</td>
                        <td class="py-2 px-4">${borrowing.borrower_name}</td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded ${
                                borrowing.status === 'aktif' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-gray-100 text-gray-800'
                            }">
                                ${borrowing.status}
                            </span>
                        </td>
                        <td class="py-2 px-4 ${borrowing.fine > 0 ? 'text-red-600 font-bold' : ''}">
                            ${borrowing.fine > 0 ? `Rp ${borrowing.fine.toLocaleString('id-ID')}` : '-'}
                        </td>
                        <td class="py-2 px-4 space-y-2">
                            ${borrowing.status === 'aktif' ? `
                                <button onclick="returnBorrowing(${borrowing.id})" 
                                    class="bg-yellow-300 text-white px-2 py-1 rounded hover:bg-blue-600 mr-2">
                                    Kembali
                                </button>
                            ` : ''}
                            <button onclick="deleteBorrowing(${borrowing.id})" 
                                class="bg-red-700 text-white px-2 py-1 rounded hover:bg-red-600">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('borrowingsList').innerHTML = 
                '<tr><td colspan="7" class="py-4 text-center text-red-600">Error loading borrowings</td></tr>';
        });
}

// Add new borrowing
document.getElementById('borrowingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/create_borrowing.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.reset();
            loadBorrowings();
            if (window.libraryWS) {
                libraryWS.sendMessage('update_borrowing', {
                    action: 'create',
                    borrowing_id: data.borrowing_id
                });
            }
        } else {
            alert(data.message || 'Gagal menambahkan peminjaman');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan peminjaman');
    });
});

// Return borrowing
function returnBorrowing(borrowingId) {
    if (confirm('Apakah Anda yakin ingin mengembalikan buku ini?')) {
        fetch('ajax/return_borrowing.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${borrowingId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadBorrowings();
                if (window.libraryWS) {
                    libraryWS.sendMessage('update_borrowing', {
                        action: 'return',
                        borrowing_id: borrowingId
                    });
                }
                if (data.fine > 0) {
                    alert(`Buku berhasil dikembalikan. Denda: Rp ${data.fine.toLocaleString('id-ID')}`);
                }
            } else {
                alert(data.message || 'Gagal mengembalikan buku');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengembalikan buku');
        });
    }
}

// Delete borrowing
function deleteBorrowing(borrowingId) {
    if (confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?')) {
        fetch(`ajax/delete_borrowing.php?id=${borrowingId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadBorrowings();
                    if (window.libraryWS) {
                        libraryWS.sendMessage('update_borrowing', {
                            action: 'delete',
                            borrowing_id: borrowingId
                        });
                    }
                } else {
                    alert(data.message || 'Gagal menghapus peminjaman');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus peminjaman');
            });
    }
}
</script>

<?php include 'includes/footer.php'; ?> 