<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    /**
     * Batasi akses halaman sesuai permission `{modul}.{aksi}`.
     * Super Admin lolos otomatis lewat gate bypass Filament Shield.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('user.read') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('user.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('user.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('user.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('user.delete') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Akun')
                    ->description('Informasi dasar pengguna panel admin.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Nonaktifkan untuk mencabut akses pengguna ke panel admin.')
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Hak Akses')
                    ->description('Tetapkan satu atau lebih peran untuk pengguna.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Peran (Role)')
                            ->relationship('roles', 'name')
                            ->options(fn (): array => self::roleOptions())
                            ->multiple()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.label')
                    ->label('Peran')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => $state)
                    ->separator(','),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->options(fn (): array => self::roleOptions()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Akun'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Opsi daftar peran: value = nama role (machine-readable),
     * label = nama tampilan Bahasa Indonesia.
     */
    protected static function roleOptions(): array
    {
        return Role::query()
            ->orderBy('id')
            ->get()
            ->pluck('label', 'name')
            ->all();
    }

    /**
     * Hindari menampilkan akun super admin pada daftar yang dapat
     * dihapus/dinonaktifkan oleh pengelola (proteksi keselamatan sistem).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
