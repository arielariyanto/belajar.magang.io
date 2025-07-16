<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiswaResource\Pages;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $pluralModelLabel = 'Siswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Form Siswa')->schema([
                Forms\Components\TextInput::make('nisn')
                    ->label('NISN')
                    ->numeric() // Hanya angka
                    ->required()
                    ->maxLength(10)
                    ->placeholder('Contoh: 1234567890'),
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Siswa')
                    ->required()
                    ->placeholder('Contoh: Ahmad Fauzan')
                    ->maxLength(100),

            Forms\Components\Select::make('kelas')
                ->label('Kelas')
                ->required()
                ->options([
                    'X' => 'X',
                    'XI' => 'XI',
                    'XII' => 'XII',
                ])
                ->searchable(),

            Forms\Components\Select::make('jurusan_id')
                ->label('Jurusan')
                ->required()
                ->options([
                    'KA' => 'KIMIA ANALIS',
                    'TEKKIN' => 'TEKNIK KIMIA INDUSTRI',
                    'TKJ' => 'TEKNIK KOMPUTER JARINGAN',
                    'RPL' => 'REKAYASA PERANGKAT LUNAK',
                    'AKL' => 'AKUNTANSI',
                ])
                ->searchable(),
            ]),
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
                ->sortable(),

            Tables\Columns\TextColumn::make('jurusan_id')
                ->label('Jurusan')
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'KA' => 'Kimia Analis',
                    'TEKKIN' => 'Teknik Kimia Industri',
                    'TKJ' => 'Teknik Komputer Jaringan',
                    'RPL' => 'Rekayasa Perangkat Lunak',
                    'AKL' => 'Akuntansi',
                    default => $state,
                }),
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
