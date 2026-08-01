<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BidangResource\Pages;
use App\Models\Bidang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BidangResource extends Resource
{
    protected static ?string $model = Bidang::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Bidang & Sub-Bagian';

    protected static ?string $modelLabel = 'Bidang';

    protected static ?string $pluralModelLabel = 'Bidang & Sub-Bagian';

    protected static ?string $navigationGroup = 'SOP';

    protected static ?int $navigationSort = 1;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('sop.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('sop.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('sop.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('sop.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('sop.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Bidang')
                    ->description('Master Bidang/Sub-Bagian yang dipakai bersama Katalog Layanan & Dokumen SOP.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Bidang/Sub-Bagian')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Components\TextInput $component, Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash()
                            ->helperText('Otomatis diisi dari nama.'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(1000)
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bidang/Sub-Bagian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('services_count')
                    ->label('Jumlah Layanan')
                    ->counts('services')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sop_documents_count')
                    ->label('Jumlah SOP')
                    ->counts('sopDocuments')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
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
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBidangs::route('/'),
            'create' => Pages\CreateBidang::route('/create'),
            'edit' => Pages\EditBidang::route('/{record}/edit'),
        ];
    }
}
