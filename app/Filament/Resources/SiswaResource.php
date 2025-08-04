<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Siswa;
use App\Models\Jurusan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SiswaResource\Pages;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $pluralModelLabel = 'Siswa';
    protected static ?string $navigationGroup = 'Manajemen Siswa';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Form Siswa')->schema([
                TextInput::make('nisn')
                    ->label('Absen Siswa')
                    ->numeric() // Hanya angka
                    ->required()
                    ->maxLength(10)
                    ->placeholder('Absen Siswa Angka'),
                TextInput::make('nama')
                    ->label('Nama Siswa')
                    ->required()
                    ->placeholder('Huruf Kapital Awal')
                    ->maxLength(100),
                Select::make('kelas')
                    ->label('Kelas')
                    ->required()
                    ->options([
                        'X' => 'X',
                        'XI' => 'XI',
                        'XII' => 'XII',
                    ])
                    ->searchable(),

                Select::make('jurusan_id')
                    ->label('Jurusan')
                    ->options(Jurusan::all()->mapWithKeys(function ($item) {
                        return [
                            $item->id => "{$item->nama_jurusan} - {$item->kode_jurusan}",
                        ];
                    }))
                    ->searchable()
                    ->required(),

            ]),
            Forms\Components\Section::make('Form Bendahara')->schema([
                Select::make('role')
                    ->label('Peran Siswa')
                    ->required()
                    ->options([
                        'siswa' => 'Siswa',
                        'bendahara' => 'Bendahara',
                    ])
                    ->reactive(),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas')
                    ->label('Kelas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jurusan.nama_jurusan')
                    ->label('Jurusan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jurusan.kode_jurusan')
                    ->label('Kode Jurusan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn($state) => $state === 'bendahara' ? 'success' : 'gray'),

            ])
            ->filters([])
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
            'index' => Pages\ListSiswas::route('/'),
            // 'create' => Pages\CreateSiswa::route('/create'),
            // 'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }
}
