<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Jurusan;
use App\Models\Bendahara;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
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
                Bendahara::with(['siswa', 'jurusan'])->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('nama_bendahara')
                    ->label('Nama Bendahara'),

                Tables\Columns\TextColumn::make('kelas')
                    ->label('Kelas'),

                Tables\Columns\TextColumn::make('jurusan.nama_jurusan')
                    ->label('Jurusan'),

                Tables\Columns\TextColumn::make('jurusan.kode_jurusan')
                    ->label('Kode Jurusan'),

                Tables\Columns\TextColumn::make('siswa.nama')
                    ->label('Nama Siswa'),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('Rp', true)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            ])
            ->filters([
                SelectFilter::make('kelas')
                    ->label('Kelas')
                    ->options([
                        'X' => 'X',
                        'XI' => 'XI',
                        'XII' => 'XII',
                    ])
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === null) {
                            return $query;
                        }
                        return $query->where('kelas', $data['value']);
                    }),
                SelectFilter::make('jurusan_id')
                    ->label('Nama Jurusan')
                    ->options(Jurusan::pluck('nama_jurusan', 'id'))
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === null) {
                            return $query;
                        }
                        return $query->where('jurusan_id', $data['value']);
                    }),

                SelectFilter::make('kode_jurusan')
                    ->label('Kode Jurusan')
                    ->options(
                        Jurusan::pluck('kode_jurusan', 'kode_jurusan')->toArray()
                    )
                    ->searchable()
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === null) {
                            return $query;
                        }
                        return $query->whereHas('jurusan', function ($q) use ($data) {
                            $q->where('kode_jurusan', $data['value']);
                        });
                    }),
            ]);
    }
}
