<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileSectionResource\Pages;
use App\Models\ProfileSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProfileSectionResource extends Resource
{
    protected static ?string $model = ProfileSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Seksi Profil';

    protected static ?string $modelLabel = 'Seksi Profil';

    protected static ?string $pluralModelLabel = 'Seksi Profil';

    protected static ?string $navigationGroup = 'Profil';

    protected static ?int $navigationSort = 3;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('profile.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('profile.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('profile.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('profile.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('profile.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Seksi')
                    ->description('Seksi konten halaman Profil publik, misalnya Visi, Misi, Program Prioritas, atau Sasaran Program. Urutan diatur lewat tabel (drag & drop).')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Seksi')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Components\TextInput $component, Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->regex('/^[a-z0-9][a-z0-9-]*$/')
                            ->helperText('Otomatis diisi dari judul. Hanya huruf kecil, angka, dan tanda hubung.'),
                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Konten')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike', 'link',
                                'orderedList', 'bulletList', 'blockquote', 'h2', 'h3', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampil di halaman Profil')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Seksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status tampil'),
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfileSections::route('/'),
            'create' => Pages\CreateProfileSection::route('/create'),
            'edit' => Pages\EditProfileSection::route('/{record}/edit'),
        ];
    }
}
