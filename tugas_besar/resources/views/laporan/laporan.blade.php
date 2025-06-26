@extends('layout.app', ['headerTitle' => 'Laporan'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Laporan Deteksi Telur</h1>

            <!-- Filter Form -->
            <form action="{{ route('laporan.index') }}" method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate ?? now()->subDays(30)->format('Y-m-d') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-egg-orange focus:ring focus:ring-egg-orange-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate ?? now()->format('Y-m-d') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-egg-orange focus:ring focus:ring-egg-orange-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="kategori"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-egg-orange focus:ring focus:ring-egg-orange-50">
                            <option value="semua" @if(($kategori ?? 'semua') == 'semua') selected @endif>Semua Kategori
                            </option>
                            <option value="MUTU 1" @if(($kategori ?? '') == 'MUTU 1') selected @endif>MUTU 1
                            </option>
                            <option value="MUTU 2" @if(($kategori ?? '') == 'MUTU 2') selected @endif>MUTU 2
                            </option>
                            <option value="MUTU 3" @if(($kategori ?? '') == 'MUTU 3') selected @endif>MUTU 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pengguna</label>
                        <select name="user_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-egg-orange focus:ring focus:ring-egg-orange-50">
                            <option value="">Semua Pengguna</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @if(($selectedUserId ?? '') == $user->id) selected @endif>
                                    {{ $user->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-filter mr-1"></i> Filter Laporan
                    </button>

                    <a href="{{ route('laporan.print') }}?{{ http_build_query(request()->all()) }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md" target="_blank">
                        <i class="fas fa-print mr-1"></i> Cetak Laporan
                    </a>

                    <a href="{{ route('laporan.export.pdf') }}?{{ http_build_query(request()->all()) }}"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                </div>
            </form>

            <!-- Statistik Ringkasan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Ringkasan Statistik</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-egg-orange-10 p-3 rounded-lg border border-egg-orange-50">
                        <span class="text-sm text-egg-orange">Total Deteksi</span>
                        <p class="text-2xl font-bold text-gray-800">{{ $detections->count() }}</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                        <span class="text-sm text-green-700">Telur MUTU 1</span>
                        <p class="text-2xl font-bold text-green-800">
                            @if($kategori == 'semua' || $kategori == 'MUTU 1')
                                {{ $totalMutu1 ?? 0 }}
                            @else
                                0
                            @endif
                        </p>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        <span class="text-sm text-yellow-700">Telur MUTU 2</span>
                        <p class="text-2xl font-bold text-yellow-800">
                            @if($kategori == 'semua' || $kategori == 'MUTU 2')
                                {{ $totalMutu2 ?? 0 }}
                            @else
                                0
                            @endif
                        </p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                        <span class="text-sm text-red-700">Telur MUTU 3</span>
                        <p class="text-2xl font-bold text-red-800">
                            @if($kategori == 'semua' || $kategori == 'MUTU 3')
                                {{ $totalMutu3 ?? 0 }}
                            @else
                                0
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Chart Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-md font-semibold mb-2">Distribusi Telur Berdasarkan Kategori</h3>
                    <div style="height: 250px">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-md font-semibold mb-2">Tren Deteksi Telur</h3>
                    <div style="height: 250px">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Data Deteksi -->
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold mb-3">Detail Deteksi</h2>
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th
                                class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                                ID</th>
                            <th
                                class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                                Tanggal</th>
                            <th
                                class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                                Nama File</th>
                            <th
                                class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                                Pengguna</th>

                            @if($kategori == 'semua' || $kategori == 'MUTU 1')
                                <th
                                    class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                                    MUTU 1</th>
                            @endif

                            @if($kategori == 'semua' || $kategori == 'MUTU 2')
                                <th
                                    class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                                    MUTU 2</th>
                            @endif

                            @if($kategori == 'semua' || $kategori == 'MUTU 3')
                                <th
                                    class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                                    MUTU 3</th>
                            @endif

                            <th
                                class="py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detections ?? [] as $detection)
                            <tr class="hover:bg-egg-orange-10">
                                <td class="py-2 px-3 border-b border-gray-200 text-sm">{{ $detection->id }}</td>
                                <td class="py-2 px-3 border-b border-gray-200 text-sm">
                                    {{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </td>
                                <td class="py-2 px-3 border-b border-gray-200 text-sm">{{ $detection->nama_file }}</td>
                                <td class="py-2 px-3 border-b border-gray-200 text-sm">{{ $detection->user->nama ?? '-' }}</td>

                                @if($kategori == 'semua' || $kategori == 'MUTU 1')
                                    <td class="py-2 px-3 border-b border-gray-200 text-sm text-center">
                                        <span class="font-medium text-green-600">{{ $detection->jumlah_mutu1 }}</span>
                                    </td>
                                @endif

                                @if($kategori == 'semua' || $kategori == 'MUTU 2')
                                    <td class="py-2 px-3 border-b border-gray-200 text-sm text-center">
                                        <span class="font-medium text-yellow-600">{{ $detection->jumlah_mutu2 }}</span>
                                    </td>
                                @endif

                                @if($kategori == 'semua' || $kategori == 'MUTU 3')
                                    <td class="py-2 px-3 border-b border-gray-200 text-sm text-center">
                                        <span class="font-medium text-red-600">{{ $detection->jumlah_mutu3 }}</span>
                                    </td>
                                @endif

                                <td class="py-2 px-3 border-b border-gray-200 text-sm text-center font-bold">
                                    @if($kategori == 'semua')
                                        {{ $detection->jumlah_mutu1 + $detection->jumlah_mutu2 + $detection->jumlah_mutu3 }}
                                    @elseif($kategori == 'MUTU 1')
                                        {{ $detection->jumlah_mutu1 }}
                                    @elseif($kategori == 'MUTU 2')
                                        {{ $detection->jumlah_mutu2 }}
                                    @elseif($kategori == 'MUTU 3')
                                        {{ $detection->jumlah_mutu3 }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($kategori == 'semua') ? 8 : 6 }}" class="py-4 text-center text-gray-500">
                                    Tidak ada data deteksi yang tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-egg-orange-10">
                            <td colspan="4" class="py-2 px-3 text-right font-semibold text-egg-orange">Total:</td>

                            @if($kategori == 'semua' || $kategori == 'MUTU 1')
                                <td class="py-2 px-3 text-center font-semibold text-green-600">
                                    {{ $totalMutu1 ?? 0 }}
                                </td>
                            @endif

                            @if($kategori == 'semua' || $kategori == 'MUTU 2')
                                <td class="py-2 px-3 text-center font-semibold text-yellow-600">
                                    {{ $totalMutu2 ?? 0 }}
                                </td>
                            @endif

                            @if($kategori == 'semua' || $kategori == 'MUTU 3')
                                <td class="py-2 px-3 text-center font-semibold text-red-600">
                                    {{ $totalMutu3 ?? 0 }}
                                </td>
                            @endif

                            <td class="py-2 px-3 text-center font-semibold">
                                @if($kategori == 'semua')
                                    {{ ($totalMutu1 ?? 0) + ($totalMutu2 ?? 0) + ($totalMutu3 ?? 0) }}
                                @elseif($kategori == 'MUTU 1')
                                    {{ $totalMutu1 ?? 0 }}
                                @elseif($kategori == 'MUTU 2')
                                    {{ $totalMutu2 ?? 0 }}
                                @elseif($kategori == 'MUTU 3')
                                    {{ $totalMutu3 ?? 0 }}
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data untuk chart - filter sesuai kategori yang dipilih
            const mutu1 = {{ ($kategori == 'semua' || $kategori == 'MUTU 1') ? ($totalMutu1 ?? 0) : 0 }};
            const mutu2 = {{ ($kategori == 'semua' || $kategori == 'MUTU 2') ? ($totalMutu2 ?? 0) : 0 }};
            const mutu3 = {{ ($kategori == 'semua' || $kategori == 'MUTU 3') ? ($totalMutu3 ?? 0) : 0 }};

            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['MUTU 1', 'MUTU 2', 'MUTU 3'],
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
                                font: { size: 10 },
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = mutu1 + mutu2 + mutu3;
                                    const value = context.raw;
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${context.label}: ${value} telur (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Line Chart untuk tren deteksi (data ini harus diambil dari controller)
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels ?? []) !!},
                    datasets: [
                        @if($kategori == 'semua' || $kategori == 'MUTU 1')
                                                                {
                                label: 'MUTU 1',
                                data: {!! json_encode($chartMutu1 ?? []) !!},
                                borderColor: 'rgba(72, 187, 120, 1)',
                                backgroundColor: 'rgba(72, 187, 120, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                        @endif
                        @if($kategori == 'semua' || $kategori == 'MUTU 2')
                                                                {
                                label: 'MUTU 2',
                                data: {!! json_encode($chartMutu2 ?? []) !!},
                                borderColor: 'rgba(237, 137, 54, 1)',
                                backgroundColor: 'rgba(237, 137, 54, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                        @endif
                        @if($kategori == 'semua' || $kategori == 'MUTU 3')
                                                                {
                                label: 'MUTU 3',
                                data: {!! json_encode($chartMutu3 ?? []) !!},
                                borderColor: 'rgba(229, 62, 62, 1)',
                                backgroundColor: 'rgba(229, 62, 62, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        @endif
                                        ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 10 } }
                        }
                    }
                }
            });
        });
    </script>
@endsection