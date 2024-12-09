<?php include 'includes/header.php'; ?>

<div class="bg-white p-5 rounded-lg shadow-md mb-5">
    <h2 class="text-xl font-bold mb-4">Tambah Buku Baru</h2>
    <form id="bookForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="text" name="title" placeholder="Judul Buku" 
            class="p-2 border rounded-lg" required>
        <input type="text" name="author" placeholder="Penulis" 
            class="p-2 border rounded-lg" required>
        <input type="text" name="publisher" placeholder="Penerbit" 
            class="p-2 border rounded-lg" required>
        <input type="number" name="year" placeholder="Tahun Terbit" 
            class="p-2 border rounded-lg" required>
        <input type="number" name="stock" placeholder="Stok" 
            class="p-2 border rounded-lg" min="0" required>
        <input type="text" name="shelf" placeholder="Nomor Rak" 
            class="p-2 border rounded-lg">
        <div class="md:col-span-3">
            <button type="submit" 
                class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                Tambah Buku
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-5 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Buku</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">Judul</th>
                    <th class="py-2 px-4 text-left">Penulis</th>
                    <th class="py-2 px-4 text-left">Penerbit</th>
                    <th class="py-2 px-4 text-left">Tahun</th>
                    <th class="py-2 px-4 text-left">Stok</th>
                    <th class="py-2 px-4 text-left">Rak</th>
                    <th class="py-2 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody id="booksList">
                <!-- Data will be loaded via WebSocket -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Buku -->
<div id="editBookModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="bg-white p-5 rounded-lg w-96 mx-auto mt-20">
        <h2 class="text-xl font-bold mb-4">Edit Data Buku</h2>
        <form id="editBookForm">
            <input type="hidden" name="id" id="editBookId">
            <div class="space-y-3">
                <input type="text" name="title" id="editBookTitle" 
                    placeholder="Judul Buku" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="text" name="author" id="editBookAuthor" 
                    placeholder="Penulis" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="text" name="publisher" id="editBookPublisher" 
                    placeholder="Penerbit" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="number" name="year" id="editBookYear" 
                    placeholder="Tahun Terbit" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="number" name="stock" id="editBookStock" 
                    placeholder="Stok" 
                    class="p-2 border rounded-lg w-full" min="0" required>
                <input type="text" name="shelf" id="editBookShelf" 
                    placeholder="Nomor Rak" 
                    class="p-2 border rounded-lg w-full">
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="submit" 
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Simpan
                </button>
                <button type="button" onclick="closeEditModal()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi untuk memuat dan menampilkan daftar buku
function loadBooks() {
    fetch('ajax/get_books.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('booksList');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="py-4 text-center">Tidak ada data buku</td></tr>';
            } else {
                tbody.innerHTML = data.map(book => `
                    <tr class="border-t">
                        <td class="py-2 px-4">${book.title}</td>
                        <td class="py-2 px-4">${book.author}</td>
                        <td class="py-2 px-4">${book.publisher}</td>
                        <td class="py-2 px-4">${book.year}</td>
                        <td class="py-2 px-4">${book.stock}</td>
                        <td class="py-2 px-4">${book.shelf || '-'}</td>
                        <td class="py-2 px-4 space-y-2">
                            <button onclick="showEditModal(${book.id})" 
                                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 mr-2">
                                ✏️
                            </button>
                            <button onclick="deleteBook(${book.id})" 
                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading books:', error);
            document.getElementById('booksList').innerHTML = 
                '<tr><td colspan="7" class="py-4 text-center text-red-600">Error loading books</td></tr>';
        });
}

// Load books when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadBooks();
});

// Update form submit handler
document.getElementById('bookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/create_book.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.reset();
            loadBooks(); // Reload books after adding new one
            if (window.libraryWS) {
                libraryWS.sendMessage('update_book', {
                    action: 'create',
                    book_id: data.book_id
                });
            }
        } else {
            alert(data.message || 'Error adding book');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding book');
    });
});

// Fungsi untuk menampilkan modal edit
function showEditModal(bookId) {
    fetch(`ajax/get_book.php?id=${bookId}`)
        .then(response => response.json())
        .then(book => {
            if (book.error) {
                throw new Error(book.message);
            }
            
            // Isi form dengan data buku
            document.getElementById('editBookId').value = book.id;
            document.getElementById('editBookTitle').value = book.title;
            document.getElementById('editBookAuthor').value = book.author;
            document.getElementById('editBookPublisher').value = book.publisher;
            document.getElementById('editBookYear').value = book.year;
            document.getElementById('editBookStock').value = book.stock;
            document.getElementById('editBookShelf').value = book.shelf || '';
            
            // Tampilkan modal
            document.getElementById('editBookModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil data buku');
        });
}

// Fungsi untuk menutup modal
function closeEditModal() {
    document.getElementById('editBookModal').classList.add('hidden');
    document.getElementById('editBookForm').reset();
}

// Event listener untuk form edit
document.getElementById('editBookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/update_book.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            loadBooks(); // Reload daftar buku
            if (window.libraryWS) {
                libraryWS.sendMessage('update_book', {
                    action: 'update',
                    book_id: formData.get('id')
                });
            }
        } else {
            alert(data.message || 'Gagal mengupdate buku');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate buku');
    });
});

// Tambahkan event listener untuk tombol close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});

function deleteBook(bookId) {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
        fetch(`ajax/delete_book.php?id=${bookId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadBooks(); // Reload daftar buku
                if (window.libraryWS) {
                    libraryWS.sendMessage('update_book', {
                        action: 'delete',
                        book_id: bookId
                    });
                }
            } else {
                alert(data.message || 'Gagal menghapus buku');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus buku');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?> 