<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendaResource\Pages;
use App\Models\Agenda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgendaResource extends Resource
{
    protected static ?string $model = Agenda::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Agenda Dinas';

    protected static ?string $modelLabel = 'Agenda';

    protected static ?string $pluralModelLabel = 'Agenda Dinas';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 5;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('agenda.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('agenda.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('agenda.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('agenda.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('agenda.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Kegiatan')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nama Kegiatan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),
                        Forms\Components\TextInput::make('start_time')
                            ->label('Waktu Mulai')
                            ->type('time'),
                        Forms\Components\TextInput::make('end_time')
                            ->label('Waktu Selesai')
                            ->type('time'),
                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pic')
                            ->label('Penanggung Jawab')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('pic')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Agenda $record): string => $record->statusLabel())
                    ->color(fn (string $state): string => match ($state) {
                        'Selesai' => 'gray',
                        'Hari Ini' => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->options(fn (): array => static::monthOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}$/', $value)) {
                            return $query;
                        }

                        [$year, $month] = explode('-', $value);

                        return $query
                            ->whereYear('date', (int) $year)
                            ->whereMonth('date', (int) $month);
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
            ])
            ->defaultSort('date', 'asc');
    }

    /**
     * Opsi filter bulan: 12 bulan terakhir (kunci `YYYY-MM`).
     *
     * @return array<string, string>
     */
    protected static function monthOptions(): array
    {
        $options = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $options[$month->format('Y-m')] = $month->translatedFormat('F Y');
        }

        return $options;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgendas::route('/'),
            'create' => Pages\CreateAgenda::route('/create'),
            'edit' => Pages\EditAgenda::route('/{record}/edit'),
        ];
    }
}
