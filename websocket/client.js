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
            case 'update_borrowing':
                this.updateBorrowingList();
                break;
            case 'update_book':
                this.updateBookList();
                break;
        }
    }

    updateBorrowingList() {
        fetch('ajax/get_borrowings.php')
            .then(response => response.json())
            .then(data => {
                this.renderBorrowings(data);
            });
    }

    updateBookList() {
        fetch('ajax/get_books.php')
            .then(response => response.json())
            .then(data => {
                this.renderBooks(data);
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

const libraryWS = new LibraryWebSocket(); 