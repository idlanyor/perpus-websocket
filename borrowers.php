<?php include 'includes/header.php'; ?>

<div class="bg-white p-5 rounded-lg shadow-md mb-5">
    <h2 class="text-xl font-bold mb-4">Tambah Peminjam Baru</h2>
    <form id="borrowerForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" name="name" placeholder="Nama Lengkap" 
            class="p-2 border rounded-lg" required>
        <input type="text" name="id_number" placeholder="Nomor Identitas" 
            class="p-2 border rounded-lg" required>
        <input type="tel" name="phone" placeholder="Nomor Telepon" 
            class="p-2 border rounded-lg" required>
        <input type="text" name="address" placeholder="Alamat" 
            class="p-2 border rounded-lg" required>
        <div class="md:col-span-2">
            <button type="submit" 
                class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                Tambah Peminjam
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-5 rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Daftar Peminjam</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">Nama</th>
                    <th class="py-2 px-4 text-left">No. Identitas</th>
                    <th class="py-2 px-4 text-left">Telepon</th>
                    <th class="py-2 px-4 text-left">Alamat</th>
                    <th class="py-2 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody id="borrowersList">
                <!-- Data will be loaded dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Peminjam -->
<div id="editBorrowerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="bg-white p-5 rounded-lg w-96 mx-auto mt-20">
        <h2 class="text-xl font-bold mb-4">Edit Data Peminjam</h2>
        <form id="editBorrowerForm">
            <input type="hidden" name="id" id="editBorrowerId">
            <div class="space-y-3">
                <input type="text" name="name" id="editBorrowerName" 
                    placeholder="Nama Lengkap" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="text" name="id_number" id="editBorrowerIdNumber" 
                    placeholder="Nomor Identitas" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="tel" name="phone" id="editBorrowerPhone" 
                    placeholder="Nomor Telepon" 
                    class="p-2 border rounded-lg w-full" required>
                <input type="text" name="address" id="editBorrowerAddress" 
                    placeholder="Alamat" 
                    class="p-2 border rounded-lg w-full" required>
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
// Load borrowers when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadBorrowers();
});

// Function to load and display borrowers
function loadBorrowers() {
    fetch('ajax/get_borrowers.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('borrowersList');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center">Tidak ada data peminjam</td></tr>';
            } else {
                tbody.innerHTML = data.map(borrower => `
                    <tr class="border-t">
                        <td class="py-2 px-4">${borrower.name}</td>
                        <td class="py-2 px-4">${borrower.id_number}</td>
                        <td class="py-2 px-4">${borrower.phone}</td>
                        <td class="py-2 px-4">${borrower.address}</td>
                        <td class="py-2 px-4">
                            <button onclick="showEditModal(${borrower.id})" 
                                class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 mr-2">
                                Edit
                            </button>
                            <button onclick="deleteBorrower(${borrower.id})" 
                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('borrowersList').innerHTML = 
                '<tr><td colspan="5" class="py-4 text-center text-red-600">Error loading borrowers</td></tr>';
        });
}

// Add new borrower
document.getElementById('borrowerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/create_borrower.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.reset();
            loadBorrowers();
            if (window.libraryWS) {
                libraryWS.sendMessage('update_borrower', {
                    action: 'create',
                    borrower_id: data.borrower_id
                });
            }
        } else {
            alert(data.message || 'Gagal menambahkan peminjam');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan peminjam');
    });
});

// Show edit modal
function showEditModal(borrowerId) {
    fetch(`ajax/get_borrower.php?id=${borrowerId}`)
        .then(response => response.json())
        .then(borrower => {
            if (borrower.error) {
                throw new Error(borrower.message);
            }
            
            document.getElementById('editBorrowerId').value = borrower.id;
            document.getElementById('editBorrowerName').value = borrower.name;
            document.getElementById('editBorrowerIdNumber').value = borrower.id_number;
            document.getElementById('editBorrowerPhone').value = borrower.phone;
            document.getElementById('editBorrowerAddress').value = borrower.address;
            
            document.getElementById('editBorrowerModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil data peminjam');
        });
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editBorrowerModal').classList.add('hidden');
    document.getElementById('editBorrowerForm').reset();
}

// Update borrower
document.getElementById('editBorrowerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/update_borrower.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            loadBorrowers();
            if (window.libraryWS) {
                libraryWS.sendMessage('update_borrower', {
                    action: 'update',
                    borrower_id: formData.get('id')
                });
            }
        } else {
            alert(data.message || 'Gagal mengupdate peminjam');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate peminjam');
    });
});

// Delete borrower
function deleteBorrower(borrowerId) {
    if (confirm('Apakah Anda yakin ingin menghapus peminjam ini?')) {
        fetch(`ajax/delete_borrower.php?id=${borrowerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadBorrowers();
                    if (window.libraryWS) {
                        libraryWS.sendMessage('update_borrower', {
                            action: 'delete',
                            borrower_id: borrowerId
                        });
                    }
                } else {
                    alert(data.message || 'Gagal menghapus peminjam');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus peminjam');
            });
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?> 