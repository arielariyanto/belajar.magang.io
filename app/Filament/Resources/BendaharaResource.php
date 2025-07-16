<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Siswa;
use App\Models\Bendahara;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\BendaharaResource\Pages;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class BendaharaResource extends Resource
{
    protected static ?string $model = Bendahara::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Data Bendahara';
    protected static ?string $pluralModelLabel = 'Bendahara';
    protected static ?string $navigationGroup = 'Manajemen Kas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama_bendahara')
                ->label('Nama Bendahara')
                ->required()
                ->maxLength(255),

            Select::make('kelas')
                ->label('Kelas')
                ->required()
                ->options([
                    'X' => 'X',
                    'XI' => 'XI',
                    'XII' => 'XII',
                ])
                ->searchable(),

            Forms\Components\Section::make('Input Kas Siswa')
                ->description('Pilih siswa berdasarkan NISN dan lihat nama otomatis.')
                ->schema([
                    Select::make('siswa_id')
                        ->label('Pilih Siswa (NISN)')
                        ->options(Siswa::all()->pluck('nisn', 'id')) // ganti 'label' jadi 'nisn'
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $siswa = Siswa::find($state);
                            if ($siswa) {
                                $set('nama_siswa', $siswa->nama);
                            }
                        }),

                    TextInput::make('nama_siswa')
                        ->label('Nama Siswa')
                        ->disabled()
                        ->dehydrated(false)
                        ->default(fn (callable $get) => Siswa::find($get('siswa_id'))?->nama),

                    TextInput::make('jumlah')
                    ->label('Jumlah Kas Dibayar')
                    ->required()
                    ->extraAttributes([
                        'x-data' => '{}',
                        'x-on:input' => "
                            \$el.value = \$el.value
                                .replace(/[^\\d]/g, '')
                                .replace(/\\B(?=(\\d{3})+(?!\\d))/g, '.');
                        ",
                        'inputmode' => 'numeric',
                        'placeholder' => 'Contoh: 50.000',
                    ])
                    ->dehydrateStateUsing(fn ($state) => str_replace('.', '', $state))
                    ->rule('numeric') // Gunakan rule validasi Laravel, bukan ->numeric()
                    ->minValue(0)
                    ->maxValue(10000000),
                                ]),
                        ]);
                    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_bendahara')->label('Nama Bendahara'),
                Tables\Columns\TextColumn::make('kelas')->label('Kelas'),
                Tables\Columns\TextColumn::make('siswa.nisn')->label('NISN Siswa'),
                Tables\Columns\TextColumn::make('siswa.nama')->label('Nama Siswa'),
                Tables\Columns\TextColumn::make('siswa.jurusan_id')->label('Jurusan'),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')  
                    ->money('IDR', true)
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignLeft(),
            
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i'),

                // Sisa Kas hanya ditampilkan sekali, bukan per baris data
                Tables\Columns\TextColumn::make('dummy') // Gunakan nama palsu
                    ->label('Sisa Kas Saat Ini')
                    ->getStateUsing(function () {
                        $pemasukan = \App\Models\Bendahara::sum('jumlah');
                        $pengeluaran = \App\Models\Pengeluaran::sum('jumlah');
                        return 'Rp ' . number_format($pemasukan - $pengeluaran, 0, ',', '.');
                    })
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'font-bold'])
                    ->alignLeft()
                    ->visible(fn () => true),
            ])

            ->filters([
                Filter::make('nama_siswa')
                    ->form([
                        TextInput::make('nama')->label('Nama Siswa'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['nama'], function ($q) use ($data) {
                            $q->whereHas('siswa', function ($sub) use ($data) {
                                $sub->where('nama', 'like', '%' . $data['nama'] . '%');
                            });
                        });
                    }),

                SelectFilter::make('kelas')
                    ->options([
                        'X' => 'X',
                        'XI' => 'XI',
                        'XII' => 'XII',
                    ])
                    ->label('Kelas'),

                Filter::make('minimum_kas')
                    ->form([
                        TextInput::make('min')->numeric()->label('Minimal Kas'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['min'], fn ($q) => $q->where('jumlah', '>=', $data['min']));
                    }),
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
            'index' => Pages\ListBendaharas::route('/'),
            // 'create' => Pages\CreateBendahara::route('/create'),
            // 'edit' => Pages\EditBendahara::route('/{record}/edit'),
        ];
    }
}
