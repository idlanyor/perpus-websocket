class LibraryWebSocket {
    constructor() {
        this.socket = new WebSocket('ws://localhost:8080');
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        this.socket.onopen = () => {
            console.log('Connected to WebSocket server');
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }

    handleMessage(data) {
        switch (data.action) {
            case 'update_book':
                this.updateBookList();
                break;
            case 'update_borrower':
                this.updateBorrowerList();
                break;
            case 'update_borrowing':
                this.updateBorrowingList();
                break;
        }
    }

    updateBookList() {
        fetch('ajax/get_books.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('booksList');
                if (tbody) {
                    tbody.innerHTML = data.map((book, index) => `
                        <tr class="border-t">
                            <td class="py-2 px-4">${book.title}</td>
                            <td class="py-2 px-4">${book.author}</td>
                            <td class="py-2 px-4">${book.publisher}</td>
                            <td class="py-2 px-4">${book.year}</td>
                            <td class="py-2 px-4">${book.stock}</td>
                            <td class="py-2 px-4">${book.shelf || '-'}</td>
                            <td class="py-2 px-4">
                                <button onclick="showEditModal(${book.id})" 
                                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 mr-2">
                                    Edit
                                </button>
                                <button onclick="deleteBook(${book.id})" 
                                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            });
    }

    updateBorrowerList() {
        fetch('ajax/get_borrowers.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('borrowersList');
                if (tbody) {
                    tbody.innerHTML = data.map((borrower) => `
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
            });
    }

    updateBorrowingList() {
        fetch('ajax/get_borrowings.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('borrowingsList');
                if (tbody) {
                    tbody.innerHTML = data.map((borrowing) => `
                        <tr class="border-t">
                            <td class="py-2 px-4">${borrowing.borrow_date}</td>
                            <td class="py-2 px-4">${borrowing.return_date}</td>
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
                                ${new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(borrowing.fine)}
                            </td>
                            <td class="py-2 px-4">
                                <button onclick="toggleBorrowingStatus(${borrowing.id})" 
                                    class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 mr-2">
                                    ${borrowing.status === 'aktif' ? 'Selesai' : 'Aktifkan'}
                                </button>
                                <button onclick="deleteBorrowing(${borrowing.id})" 
                                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            });
    }

    sendMessage(action, data) {
        if (this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                action: action,
                data: data
            }));
        }
    }
}