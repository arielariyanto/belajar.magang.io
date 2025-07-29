<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Bendahara;
use Filament\Widgets\TableWidget as BaseWidget;

class BendaharaTable extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = '📋 Data Bendahara Terbaru';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Bendahara::with(['user', 'siswa'])->latest()->take(10)
            )
            ->columns([
                // ...
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('nama_bendahara')
                    ->label('Nama Bendahara'),

                Tables\Columns\TextColumn::make('siswa.nama')
                    ->label('Nama Siswa'),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('Rp')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            ]);
    }
}
