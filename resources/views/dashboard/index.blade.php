@extends('layout.app')

@section('title', 'Dashboard Deteksi Telur')

@section('content')
    <style>
        .chart-container {
            height: 300px;
            position: relative;
            margin: auto;
        }

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
            border-left-color: #F8A057;
            /* Ubah ke egg-orange */
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
    <div class="container mx-auto">
        <!-- Statistik Ringkasan - Kompak dan Informatif -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col">
                <h2 class="text-sm font-semibold text-gray-700">Total Gambar Dianalisis</h2>
                <p class="text-2xl font-bold text-egg-orange mb-1">{{ $totalDetections }}</p>
                <p class="text-xs text-gray-500 mt-auto">
                    <span class="font-medium">Hari ini: </span>
                    {{ $todayDetections ?? 0 }} gambar
                </p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col">
                <h2 class="text-sm font-semibold text-gray-700">Total Telur Terdeteksi</h2>
                <p class="text-2xl font-bold text-egg-orange mb-1">{{ $totalEggs }}</p>
                <p class="text-xs text-gray-500 mt-auto">
                    <span class="font-medium">Rata-rata: </span>
                    {{ $totalDetections > 0 ? round($totalEggs / $totalDetections, 1) : 0 }} telur/gambar
                </p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-sm font-semibold text-gray-700 mb-1">Distribusi Mutu</h2>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span>
                        <span class="text-gray-600">Mutu 1:</span>
                        <span
                            class="font-bold ml-1">{{ $totalEggs > 0 ? round($eggCounts['MUTU 1'] / $totalEggs * 100) : 0 }}%</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full mr-1"></span>
                        <span class="text-gray-600">Mutu 2:</span>
                        <span
                            class="font-bold ml-1">{{ $totalEggs > 0 ? round($eggCounts['MUTU 2'] / $totalEggs * 100) : 0 }}%</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-1"></span>
                        <span class="text-gray-600">Mutu 3:</span>
                        <span
                            class="font-bold ml-1">{{ $totalEggs > 0 ? round($eggCounts['MUTU 3'] / $totalEggs * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Stats & Daftar Pengguna -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Quick Stats dengan Tren (menggantikan statistik per kategori) -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-3 text-gray-700">Quick Stats</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Deteksi Minggu Ini</h3>
                            <span
                                class="text-xs px-2 py-1 rounded-full {{ $weeklyGrowth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $weeklyGrowth >= 0 ? '+' : '' }}{{ $weeklyGrowth }}%
                            </span>
                        </div>
                        <p class="text-lg font-bold text-gray-800">{{ $weeklyDetections }}</p>
                        <div class="text-xs text-gray-500 mt-1">vs minggu lalu</div>
                    </div>

                    <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Telur Kualitas Terbaik</h3>
                            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                                Mutu 1
                            </span>
                        </div>
                        <p class="text-lg font-bold text-gray-800">{{ $eggCounts['MUTU 1'] }}</p>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $eggCounts['MUTU 1'] }} total deteksi bulan ini
                        </div>
                    </div>

                    <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Telur Mutu Standar</h3>
                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                Mutu 2
                            </span>
                        </div>
                        <p class="text-lg font-bold text-gray-800">{{ $eggCounts['MUTU 2'] }}</p>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $eggCounts['MUTU 2'] }} total deteksi bulan ini
                        </div>
                    </div>

                    <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Telur Mutu Rendah</h3>
                            <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">
                                Mutu 3
                            </span>
                        </div>
                        <p class="text-lg font-bold text-gray-800">{{ $eggCounts['MUTU 3'] }}</p>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $eggCounts['MUTU 3'] }} total deteksi bulan ini
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Pengguna dengan AJAX pagination -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-3 text-gray-700">Daftar Pengguna</h2>
                <div class="overflow-x-auto" id="users-container">
                    <!-- Mengganti dengan include partial view -->
                    @include('dashboard.partials.users-table')
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-md font-semibold mb-2 text-gray-700">Distribusi Telur per Kategori</h2>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-md font-semibold mb-2 text-gray-700">Perbandingan Jumlah Telur</h2>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Detections -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Deteksi Terbaru</h2>
            <div class="overflow-x-auto" id="detections-container">
                <!-- Mengganti dengan include partial view -->
                @include('dashboard.partials.detections-table')
            </div>
        </div>

        <!-- Include Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Data jumlah telur (bukan jumlah gambar)
            const mutu1 = {{ $eggCounts['MUTU 1'] }};
            const mutu2 = {{ $eggCounts['MUTU 2'] }};
            const mutu3 = {{ $eggCounts['MUTU 3'] }};

            console.log("Chart data (telur):", { mutu1, mutu2, mutu3 });

            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['Mutu 1', 'Mutu 2', 'Mutu 3'],
                    datasets: [{
                        data: [mutu1, mutu2, mutu3],
                        backgroundColor: [
                            'rgba(72, 187, 120, 0.7)',
                            'rgba(237, 137, 54, 0.7)',
                            'rgba(229, 62, 62, 0.7)'
                        ],
                        borderColor: [
                            'rgba(72, 187, 120, 1)',
                            'rgba(237, 137, 54, 1)',
                            'rgba(229, 62, 62, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 10
                                },
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} telur (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Bar Chart
            const barCtx = document.getElementById('barChart').getContext('2d');
            const barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Mutu 1', 'Mutu 2', 'Mutu 3'],
                    datasets: [{
                        label: 'Jumlah Telur',
                        data: [mutu1, mutu2, mutu3],
                        backgroundColor: [
                            'rgba(72, 187, 120, 0.7)',
                            'rgba(237, 137, 54, 0.7)',
                            'rgba(229, 62, 62, 0.7)'
                        ],
                        borderColor: [
                            'rgba(72, 187, 120, 1)',
                            'rgba(237, 137, 54, 1)',
                            'rgba(229, 62, 62, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 9
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 9
                                }
                            }
                        }
                    },
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `${context.label}: ${context.raw} telur`;
                                }
                            }
                        }
                    }
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                setupAjaxPagination();
                setupUsersAjaxPagination();
            });

            function setupAjaxPagination() {
                const paginationLinks = document.querySelectorAll('.pagination-link');

                paginationLinks.forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (this.classList.contains('cursor-not-allowed')) {
                            return;
                        }


                        const url = this.getAttribute('href');

                        window.history.pushState({}, '', url);

                        const container = document.getElementById('detections-container');
                        container.innerHTML = `
                                                <div class="loading-spinner">
                                                    <div class="spinner"></div>
                                                </div>
                                            `;

                        fetchDetections(url);
                    });
                });

                window.addEventListener('popstate', function () {
                    fetchDetections(window.location.href);
                });
            }

            function fetchDetections(url) {
                url = url + (url.includes('?') ? '&' : '?') + 'ajax=1';

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        document.getElementById('detections-container').innerHTML = html;

                        setupAjaxPagination();
                    })
                    .catch(error => {
                        console.error('Error fetching detections:', error);
                        document.getElementById('detections-container').innerHTML = `
                                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                                    <p class="font-bold">Error</p>
                                                    <p>Terjadi kesalahan saat memuat data.</p>
                                                    <button onclick="fetchDetections('${url}')" class="mt-2 bg-egg-orange hover:bg-egg-orange-dark text-white font-bold py-1 px-3 rounded transition duration-200">
                                                        Coba Lagi
                                                    </button>
                                                </div>
                                            `;
                    });
            }

            // New functions for Users table AJAX pagination
            function setupUsersAjaxPagination() {
                const paginationLinks = document.querySelectorAll('.users-pagination-link');

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

                // Support browser back/forward buttons for users pagination
                window.addEventListener('popstate', function () {
                    // Check if current URL has users page parameter
                    if (window.location.href.includes('users_page=')) {
                        fetchUsers(window.location.href);
                    }
                });
            }

            function fetchUsers(url) {
                // Add AJAX parameter to URL
                url = url + (url.includes('?') ? '&' : '?') + 'ajax_users=1';

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
                        setupUsersAjaxPagination();
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                        document.getElementById('users-container').innerHTML = `
                                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                                    <p class="font-bold">Error</p>
                                                    <p>Terjadi kesalahan saat memuat data pengguna.</p>
                                                    <button onclick="fetchUsers('${url}')" class="mt-2 bg-egg-orange hover:bg-egg-orange-dark text-white font-bold py-1 px-3 rounded transition duration-200">
                                                        Coba Lagi
                                                    </button>
                                                </div>
                                            `;
                    });
            }
        </script>
    </div>
@endsection