<?php

namespace App\Http\Controllers;

use App\Models\DeteksiTelur;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $kategori = $request->kategori ?? 'semua';
        $selectedUserId = $request->user_id;

        $query = DeteksiTelur::whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
        }

        if ($kategori && $kategori != 'semua') {
            if ($kategori == 'MUTU 1') {
                $query->where('jumlah_mutu1', '>', 0);
            } elseif ($kategori == 'MUTU 2') {
                $query->where('jumlah_mutu2', '>', 0);
            } elseif ($kategori == 'MUTU 3') {
                $query->where('jumlah_mutu3', '>', 0);
            }
        }

        $detections = $query->get();

        $totalMutu1 = $detections->sum('jumlah_mutu1');
        $totalMutu2 = $detections->sum('jumlah_mutu2');
        $totalMutu3 = $detections->sum('jumlah_mutu3');

        $chartData = $this->getChartData($startDate, $endDate, $kategori, $selectedUserId);
        $chartLabels = $chartData['chartLabels'];
        $chartMutu1 = $chartData['chartMutu1'];
        $chartMutu2 = $chartData['chartMutu2'];
        $chartMutu3 = $chartData['chartMutu3'];

        $users = User::select('id', 'nama')->orderBy('nama')->get();

        return view('laporan.laporan', compact(
            'detections',
            'startDate',
            'endDate',
            'kategori',
            'selectedUserId',
            'totalMutu1',
            'totalMutu2',
            'totalMutu3',
            'users',
            'chartLabels',
            'chartMutu1',
            'chartMutu2',
            'chartMutu3'
        ));
    }

    private function getChartData($startDate, $endDate, $kategori, $userId)
    {
        $period = Carbon::parse($startDate)->daysUntil($endDate);

        $chartLabels = [];
        $chartMutu1 = [];
        $chartMutu2 = [];
        $chartMutu3 = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('d M');
            $chartLabels[] = $formattedDate;

            $query = DeteksiTelur::whereDate('created_at', $date);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $result = $query->selectRaw('SUM(jumlah_mutu1) as total_mutu1, SUM(jumlah_mutu2) as total_mutu2, SUM(jumlah_mutu3) as total_mutu3')
                ->first();

            $chartMutu1[] = $result->total_mutu1 ?? 0;
            $chartMutu2[] = $result->total_mutu2 ?? 0;
            $chartMutu3[] = $result->total_mutu3 ?? 0;
        }

        return [
            'chartLabels' => $chartLabels,
            'chartMutu1' => $chartMutu1,
            'chartMutu2' => $chartMutu2,
            'chartMutu3' => $chartMutu3
        ];
    }

    public function printReport(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $kategori = $request->kategori ?? 'semua';
        $selectedUserId = $request->user_id;

        $query = DeteksiTelur::whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
        }

        if ($kategori && $kategori != 'semua') {
            if ($kategori == 'MUTU 1') {
                $query->where('jumlah_mutu1', '>', 0);
            } elseif ($kategori == 'MUTU 2') {
                $query->where('jumlah_mutu2', '>', 0);
            } elseif ($kategori == 'MUTU 3') {
                $query->where('jumlah_mutu3', '>', 0);
            }
        }

        $detections = $query->get();

        $totalMutu1 = $detections->sum('jumlah_mutu1');
        $totalMutu2 = $detections->sum('jumlah_mutu2');
        $totalMutu3 = $detections->sum('jumlah_mutu3');

        $pdf = PDF::loadView('laporan.print', compact(
            'detections',
            'startDate',
            'endDate',
            'kategori',
            'totalMutu1',
            'totalMutu2',
            'totalMutu3'
        ));

        return $pdf->stream('laporan-deteksi-telur-' . now()->format('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        return back()->with('error', 'Fitur export excel masih dalam pengembangan');
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $kategori = $request->kategori ?? 'semua';

        $query = DeteksiTelur::whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan user_id jika ada
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan kategori jika tidak 'semua'
        if ($kategori !== 'semua') {
            if ($kategori == 'MUTU 1') {
                $query->where('jumlah_mutu1', '>', 0);
            } elseif ($kategori == 'MUTU 2') {
                $query->where('jumlah_mutu2', '>', 0);
            } elseif ($kategori == 'MUTU 3') {
                $query->where('jumlah_mutu3', '>', 0);
            }
        }

        $detections = $query->get();

        $totalMutu1 = $detections->sum('jumlah_mutu1');
        $totalMutu2 = $detections->sum('jumlah_mutu2');
        $totalMutu3 = $detections->sum('jumlah_mutu3');

        $pdf = PDF::loadView('laporan.print', compact(
            'detections',
            'startDate',
            'endDate',
            'totalMutu1',
            'totalMutu2',
            'totalMutu3',
            'kategori'
        ));

        return $pdf->download('laporan-deteksi-telur-' . now()->format('Ymd') . '.pdf');
    }
}