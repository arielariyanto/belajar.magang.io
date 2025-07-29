<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Jurusan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\JurusanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\JurusanResource\RelationManagers;

class JurusanResource extends Resource
{
    protected static ?string $model = Jurusan::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Data Siswa Jurusan';
    protected static ?string $pluralModelLabel = 'Jurusan';
    protected static ?string $navigationGroup = 'Manajemen Siswa';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('nama_jurusan')
                    ->label('Nama Jurusan')
                    ->required(),

                Select::make('kode_jurusan')
                    ->label('Kode Jurusan')
                    ->options([
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('nama_jurusan')->label('Nama Jurusan')->searchable(),
                TextColumn::make('kode_jurusan')->label('Kode'),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurusans::route('/'),
            // 'create' => Pages\CreateJurusan::route('/create'),
            // 'edit' => Pages\EditJurusan::route('/{record}/edit'),
        ];
    }
}
