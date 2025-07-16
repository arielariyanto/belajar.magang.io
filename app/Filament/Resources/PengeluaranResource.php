<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Bendahara;
use Filament\Tables\Table;
use App\Models\Pengeluaran;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\PengeluaranResource\Pages;
use Filament\Forms\Components\Group;

class PengeluaranResource extends Resource
{
    protected static ?string $model = Pengeluaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Data Pengeluaran';
    protected static ?string $pluralModelLabel = 'Pengeluaran';
    protected static ?string $navigationGroup = 'Manajemen Kas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('bendahara_id')
                    ->label('Nama Bendahara')
                    ->options(Bendahara::all()->pluck('nama_bendahara', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->required(),

                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),

                TextInput::make('jumlah')
                    ->label('Jumlah Pengeluaran')
                    ->numeric()
                    ->required(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(255),

                TextInput::make('sisa_kas_saat_ini')
                    ->label('💰 Kas Saat Ini')
                    ->disabled()
                    ->dehydrated(false) // agar tidak dikirim ke database
                    ->default(function () {
                        $pemasukan = \App\Models\Bendahara::sum('jumlah');
                        $pengeluaran = \App\Models\Pengeluaran::sum('jumlah');
                        return 'Rp ' . number_format($pemasukan - $pengeluaran, 0, ',', '.');
                    })
                    ->extraAttributes(['class' => 'text-danger font-bold text-xl']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bendahara.nama_bendahara')->label('Nama Bendahara'),
                Tables\Columns\TextColumn::make('nama_kegiatan')->label('Nama Kegiatan'),
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('jumlah')->label('Jumlah')
                    ->money('IDR', true)
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('keterangan')->label('Keterangan')->wrap(),

                Tables\Columns\TextColumn::make('sisa_kas')
                    ->label('💰 Sisa Kas Saat Ini')
                    ->getStateUsing(function () {
                        $pemasukan = \App\Models\Bendahara::sum('jumlah');
                        $pengeluaran = \App\Models\Pengeluaran::sum('jumlah');
                        $sisa = $pemasukan - $pengeluaran;
                        return 'Rp ' . number_format($sisa, 0, ',', '.');
                    })
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'font-bold text-green-700 text-right'])
                    ->visible(fn () => true), // biar selalu tampil
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);


    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengeluarans::route('/'),
            // 'create' => Pages\CreatePengeluaran::route('/create'),
            // 'edit' => Pages\EditPengeluaran::route('/{record}/edit'),
        ];
    }
}
