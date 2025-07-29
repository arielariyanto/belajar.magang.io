<?php

namespace App\Filament\Widgets;

use App\Models\Bendahara;
use App\Models\Pengeluaran;
use Filament\Widgets\ChartWidget;

class GrafikKas extends ChartWidget
{
    protected static ?string $heading = '📈 Grafik Kas (Pemasukan, Pembayaran)';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 1;



    protected function getData(): array
    {
        $tanggalAwal = now()->startOfMonth()->toDateString();
        $tanggalAkhir = now()->endOfMonth()->toDateString();


        // Ambil pemasukan per hari
        $pemasukan = Bendahara::selectRaw('DATE(created_at) as tanggal, SUM(jumlah) as total')
            ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->where('jumlah', '>', 0)
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->pluck('total', 'tanggal');


        // Ambil pengeluaran per hari
        $pengeluaran = Pengeluaran::selectRaw('DATE(created_at) as tanggal, SUM(jumlah) as total')
            ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->where('jumlah', '>', 0)
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->pluck('total', 'tanggal');


        // Gabungkan semua tanggal
        $semuaTanggal = array_unique(array_merge($pemasukan->keys()->all(), $pengeluaran->keys()->all()));
        sort($semuaTanggal);

        $labels = [];
        $pemasukanData = [];
        $pengeluaranData = [];

        foreach ($semuaTanggal as $tanggal) {
            $labels[] = \Carbon\Carbon::parse($tanggal)->format('d M');
            $pemasukanData[] = $pemasukan[$tanggal] ?? 0;
            $pengeluaranData[] = $pengeluaran[$tanggal] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan (Rp)',
                    'data' => $pemasukanData,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pengeluaran (Rp)',
                    'data' => $pengeluaranData,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
