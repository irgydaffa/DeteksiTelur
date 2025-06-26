<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DeteksiTelur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Statistik Dasar
        $totalDetections = DeteksiTelur::count();
        $totalEggs = DeteksiTelur::sum('jumlah_mutu1') + DeteksiTelur::sum('jumlah_mutu2') + DeteksiTelur::sum('jumlah_mutu3');

        // Data tambahan untuk Quick Stats dan tren
        $todayDetections = DeteksiTelur::whereDate('created_at', Carbon::today())->count();

        $lastWeekStart = Carbon::now()->subDays(14);
        $lastWeekEnd = Carbon::now()->subDays(7);
        $thisWeekStart = Carbon::now()->subDays(7);

        $weeklyDetections = DeteksiTelur::where('created_at', '>=', $thisWeekStart)->count();
        $lastWeekDetections = DeteksiTelur::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        // Menghindari division by zero
        $weeklyGrowth = $lastWeekDetections > 0
            ? round((($weeklyDetections - $lastWeekDetections) / $lastWeekDetections) * 100)
            : ($weeklyDetections > 0 ? 100 : 0);

        // Deteksi terakhir
        $lastDetection = DeteksiTelur::with('user')->latest()->first();
        $lastDetectionTime = $lastDetection ? $lastDetection->created_at : null;
        $lastDetectionUser = $lastDetection && $lastDetection->user ? $lastDetection->user->nama : 'Unknown';

        // Pengguna aktif dalam 7 hari
        $activeUsers = User::whereHas('detections', function ($query) use ($thisWeekStart) {
            $query->where('created_at', '>=', $thisWeekStart);
        })->count();

        // Menghitung jumlah telur per kategori
        $eggCounts = [
            'MUTU 1' => DeteksiTelur::sum('jumlah_mutu1'),
            'MUTU 2' => DeteksiTelur::sum('jumlah_mutu2'),
            'MUTU 3' => DeteksiTelur::sum('jumlah_mutu3'),
        ];

        // AJAX pagination untuk user table
        $usersPerPage = 5;
        $usersPage = $request->input('users_page', 1);

        $users = User::orderBy('nama')
            ->paginate($usersPerPage, ['*'], 'users_page', $usersPage);

        if ($request->ajax_users) {
            return view('dashboard.partials.users-table', compact('users'));
        }

        // Mengambil data deteksi terbaru
        $perPage = 10;
        $detections = DeteksiTelur::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        if ($request->ajax) {
            return view('dashboard.partials.detections-table', compact('detections'));
        }

        return view('dashboard.index', compact(
            'totalDetections',
            'totalEggs',
            'eggCounts',
            'detections',
            'users',
            'todayDetections',
            'weeklyDetections',
            'weeklyGrowth',
            'lastDetectionTime',
            'lastDetectionUser',
            'activeUsers'
        ));
    }

    public function telur()
    {
        $detections = DeteksiTelur::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.telur', compact('detections'));
    }

    public function users()
    {
        $users = User::orderBy('nama')
            ->paginate(10);

        return view('dashboard.users', compact('users'));
    }

    public function laporan()
    {
        // Menghitung jumlah telur per kategori
        $eggCounts = [
            'MUTU 1' => DeteksiTelur::sum('jumlah_mutu1'),
            'MUTU 2' => DeteksiTelur::sum('jumlah_mutu2'),
            'MUTU 3' => DeteksiTelur::sum('jumlah_mutu3'),
        ];

        // Mengambil data dari 7 hari terakhir (dikelompokkan per hari)
        $last7Days = Carbon::now()->subDays(6);

        $dailyDetections = DeteksiTelur::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $last7Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyEggs = DeteksiTelur::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(jumlah_mutu1) as mutu1'),
            DB::raw('SUM(jumlah_mutu2) as mutu2'),
            DB::raw('SUM(jumlah_mutu3) as mutu3')
        )
            ->where('created_at', '>=', $last7Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Mengambil data untuk tren bulanan (6 bulan terakhir)
        $last6Months = Carbon::now()->subMonths(5)->startOfMonth();

        $monthlyDetections = DeteksiTelur::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $last6Months)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $monthlyEggs = DeteksiTelur::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(jumlah_mutu1) as mutu1'),
            DB::raw('SUM(jumlah_mutu2) as mutu2'),
            DB::raw('SUM(jumlah_mutu3) as mutu3')
        )
            ->where('created_at', '>=', $last6Months)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Statistik per pengguna (top users)
        $topUsers = User::withCount('detections')
            ->orderBy('detections_count', 'desc')
            ->limit(5)
            ->get();

        // Format data untuk Chart.js
        $dates = [];
        $counts = [];
        $chartData = [];

        foreach ($dailyDetections as $item) {
            $date = Carbon::parse($item->date)->format('d/m');
            $dates[] = $date;
            $counts[] = $item->count;

            $chartData[$date] = [
                'count' => $item->count,
                'mutu1' => 0,
                'mutu2' => 0,
                'mutu3' => 0
            ];
        }

        foreach ($dailyEggs as $item) {
            $date = Carbon::parse($item->date)->format('d/m');
            $chartData[$date]['mutu1'] = (int) $item->mutu1;
            $chartData[$date]['mutu2'] = (int) $item->mutu2;
            $chartData[$date]['mutu3'] = (int) $item->mutu3;
        }

        $monthNames = [];
        $monthlyCounts = [];
        $monthlyChartData = [];

        foreach ($monthlyDetections as $item) {
            $monthName = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
            $monthNames[] = $monthName;
            $monthlyCounts[] = $item->count;

            $monthlyChartData[$monthName] = [
                'count' => $item->count,
                'mutu1' => 0,
                'mutu2' => 0,
                'mutu3' => 0
            ];
        }

        foreach ($monthlyEggs as $item) {
            $monthName = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
            $monthlyChartData[$monthName]['mutu1'] = (int) $item->mutu1;
            $monthlyChartData[$monthName]['mutu2'] = (int) $item->mutu2;
            $monthlyChartData[$monthName]['mutu3'] = (int) $item->mutu3;
        }

        return view('dashboard.laporan', compact(
            'eggCounts',
            'dates',
            'counts',
            'chartData',
            'monthNames',
            'monthlyCounts',
            'monthlyChartData',
            'topUsers'
        ));
    }
}