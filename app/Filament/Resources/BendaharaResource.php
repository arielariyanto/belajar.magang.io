<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Siswa;
use App\Models\Jurusan;
use Filament\Forms\Form;
use App\Models\Bendahara;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\BendaharaResource\Pages;

class BendaharaResource extends Resource
{
    protected static ?string $model = Bendahara::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Data Bendahara';
    protected static ?string $pluralModelLabel = 'Bendahara';
    protected static ?string $navigationGroup = 'Manajemen Kas';
    protected static ?int $navigationSort = 3;

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
                ->searchable()
                ->reactive(),

            Select::make('jurusan_id')
                ->label('Jurusan')
                ->options(Jurusan::all()->mapWithKeys(function ($item) {
                    return [
                        $item->kode_jurusan => "{$item->nama_jurusan} - {$item->kode_jurusan}",
                    ];
                }))
                ->required()
                ->searchable(),

            Forms\Components\Section::make('Input Kas Siswa')
                ->description('Pilih siswa berdasarkan NISN')
                ->schema([
                    Select::make('siswa_id')
                        ->label('Pilih Siswa (NISN)')
                        ->required()
                        ->searchable()
                        ->reactive()
                        ->options(function (callable $get) {
                            $kelas = $get('kelas');
                            $jurusanId = $get('jurusan_id');

                            if (!$kelas || !$jurusanId) {
                                return [];
                            }

                            return \App\Models\Siswa::where('kelas', $kelas)
                                ->where('jurusan_id', $jurusanId)
                                ->get()
                                ->mapWithKeys(function ($siswa) {
                                    return [$siswa->id => $siswa->nisn . ' - ' . $siswa->nama];
                                })
                                ->toArray();
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            $siswa = \App\Models\Siswa::find($state);
                            if ($siswa) {
                                $set('nama_siswa', $siswa->nama);
                            }
                        }),

                    TextInput::make('nama_siswa')
                        ->label('Nama Siswa')
                        ->disabled()
                        ->dehydrated(false)
                        ->default(fn(callable $get) => Siswa::find($get('siswa_id'))?->nama),

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
                        ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state))
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
                Tables\Columns\TextColumn::make('nama_bendahara')->label('Nama Bendahara')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas')->label('Kelas'),
                Tables\Columns\TextColumn::make('siswa.nisn')->label('NISN Siswa'),
                Tables\Columns\TextColumn::make('siswa.nama')->label('Nama Siswa'),
                Tables\Columns\TextColumn::make('jurusan.nama_jurusan')
                    ->label('Jurusan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jurusan.kode_jurusan')
                    ->label('Kode Jurusan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR', true)
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignLeft(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i'),

                // Sisa Kas hanya ditampilkan sekali, bukan per baris data
                Tables\Columns\TextColumn::make('dummy') // Gunakan nama palsu
                    ->label('Total Kas Saat Ini')
                    ->getStateUsing(function ($record) {
                        $total = \App\Models\Bendahara::where('created_at', '<=', $record->created_at)
                            ->orderBy('created_at')
                            ->sum('jumlah');
                        return 'Rp ' . number_format($total, 0, ',', '.');
                    })
                    ->alignLeft()
                    ->sortable(),
            ])

            // ->filters([
            //     Filter::make('nama_siswa')
            //         ->form([
            //             TextInput::make('nama')->label('Nama Siswa'),
            //         ])
            //         ->query(function ($query, array $data) {
            //             return $query->when($data['nama'], function ($q) use ($data) {
            //                 $q->whereHas('siswa', function ($sub) use ($data) {
            //                     $sub->where('nama', 'like', '%' . $data['nama'] . '%');
            //                 });
            //             });
            //         }),

            //     SelectFilter::make('kelas')
            //         ->options([
            //             'X' => 'X',
            //             'XI' => 'XI',
            //             'XII' => 'XII',
            //         ])
            //         ->label('Kelas'),

            //     Filter::make('minimum_kas')
            //     // ->form([
            //     //     TextInput::make('min')->numeric()->label('Minimal Kas'),
            //     // ])
            //     // ->query(function ($query, array $data) {
            //     //     return $query->when($data['min'], fn($q) => $q->where('jumlah', '>=', $data['min']));
            //     // }),
            // ])
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
