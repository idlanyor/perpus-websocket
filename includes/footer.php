    </div>


    <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600">
        <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">
            <button type="button" onclick="window.location.href='/'" class="inline-flex flex-col items-center justify-center px-5 border-e border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 group dark:border-gray-600">
                <i class="fa fa-people-arrows text-gray-500 text-xl group-hover:text-blue-600 "></i>
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Transaksi</span>
            </button>
            <button type="button" onclick="window.location.href='books.php'" class="inline-flex flex-col items-center justify-center px-5 border-e border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 group dark:border-gray-600">
                <i class="fa fa-book text-gray-500 text-xl group-hover:text-blue-600 "></i>
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Buku</span>
            </button>
            <button type="button" onclick="window.location.href='borrowers.php'" class="inline-flex flex-col items-center justify-center px-5 border-e border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 group dark:border-gray-600">
                <i class="fa fa-people-group text-gray-500 text-xl group-hover:text-blue-600 "></i>
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Peminjam</span>
            </button>
            <button type="button" onclick="window.location.href='settings.php'" class="inline-flex flex-col items-center justify-center px-5 border-e border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 group dark:border-gray-600">
                <i class="fa fa-sliders text-gray-500 text-xl group-hover:text-blue-600 "></i>
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Pengaturan</span>
            </button>

        </div>
    </div>

    <script>
        // Initialize WebSocket connection
        document.addEventListener('DOMContentLoaded', function() {
            window.libraryWS = new LibraryWebSocket();
        });
    </script>
    </body>

    </html>