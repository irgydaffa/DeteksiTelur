@extends('layout.app', ['headerTitle' => 'Manajemen Pengguna'])

@section('content')
    <style>
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3f83f8;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
            <a href="{{ route('admin.create') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Tambah Pengguna
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="flex">
                <div class="relative flex-grow">
                    <input type="text" id="search-input"
                        class="w-full rounded-l-md border border-gray-300 px-4 py-2 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-egg-orange focus:border-transparent"
                        placeholder="Cari pengguna..." value="{{ request('search') }}">
                    <div id="search-clear"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer {{ request('search') ? '' : 'hidden' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <button id="search-button"
                    class="bg-egg-orange hover:bg-egg-orange-dark text-white px-4 py-2 rounded-r-md flex items-center transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                    Cari
                </button>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden" id="users-container">
            <!-- Content will be loaded here via AJAX -->
            @include('admin.partials.users-table')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setupAjaxPagination();
            setupSearch();
        });

        function setupSearch() {
            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');
            const searchClear = document.getElementById('search-clear');

            // Search when button is clicked
            searchButton.addEventListener('click', performSearch);

            // Search when Enter key is pressed
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Show/hide clear button based on input
            searchInput.addEventListener('input', function () {
                searchClear.classList.toggle('hidden', !this.value);
            });

            // Clear search
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                this.classList.add('hidden');
                performSearch();
            });
        }

        function performSearch() {
            const searchValue = document.getElementById('search-input').value.trim();

            // Build the URL with search parameter
            let url = new URL(window.location.href);
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page'); // Reset to first page when searching

            // Update browser URL
            window.history.pushState({}, '', url);

            // Show loading indicator
            const container = document.getElementById('users-container');
            container.innerHTML = `
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                `;

            // Fetch results
            fetchUsers(url.toString());
        }

        function setupAjaxPagination() {
            const paginationLinks = document.querySelectorAll('.pagination-link');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Don't process disabled links
                    if (this.classList.contains('cursor-not-allowed')) {
                        return;
                    }

                    // Get URL from link
                    const url = this.getAttribute('href');

                    // Update URL in address bar without refresh
                    window.history.pushState({}, '', url);

                    // Show loading indicator
                    const container = document.getElementById('users-container');
                    container.innerHTML = `
                            <div class="loading-spinner">
                                <div class="spinner"></div>
                            </div>
                        `;

                    // Fetch data via AJAX
                    fetchUsers(url);
                });
            });

            // Support browser back/forward buttons
            window.addEventListener('popstate', function () {
                fetchUsers(window.location.href);
            });
        }

        function fetchUsers(url) {
            // Add AJAX parameter to URL
            url = url + (url.includes('?') ? '&' : '?') + 'ajax=1';

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    // Update container with fetched content
                    document.getElementById('users-container').innerHTML = html;

                    // Re-setup pagination after content update
                    setupAjaxPagination();
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                    document.getElementById('users-container').innerHTML = `
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <p class="font-bold">Error</p>
                                <p>Terjadi kesalahan saat memuat data.</p>
                                <button onclick="fetchUsers('${url}')" class="mt-2 bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                    Coba Lagi
                                </button>
                            </div>
                        `;
                });
        }
    </script>
@endsection