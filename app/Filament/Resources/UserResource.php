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
use Illuminate\Database\Eloquent\Collection;

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
        if (! auth()->user()?->can('user.delete')) {
            return false;
        }

        if (! $record instanceof User) {
            return false;
        }

        if (static::isProtectedAccount($record)) {
            return false;
        }

        return true;
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
                            ->default(true)
                            ->disabled(fn (?User $record): bool => static::isProtectedAccount($record)),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Hak Akses')
                    ->description('Tetapkan satu atau lebih peran untuk pengguna.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Peran (Role)')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Role $record): string => $record->label)
                            ->multiple()
                            ->preload()
                            ->required()
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records
                                ->filter(fn (User $record): bool => static::canDelete($record))
                                ->each->delete();

                            $this->success();
                        }),
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
     * Opsi filter peran: value = nama role (machine-readable, cocok
     * dengan SelectFilter relationship), label = nama tampilan Indonesia.
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
     * Akun yang tidak boleh dinonaktifkan:
     * - akun yang sedang login (mencegah kunci sendiri),
     * - super admin aktif terakhir (mencegah sistem tanpa super admin).
     */
    public static function isProtectedAccount(?User $record): bool
    {
        if ($record === null) {
            return false;
        }

        if ($record->is(auth()->user())) {
            return true;
        }

        if (! $record->is_active) {
            return false;
        }

        if (! $record->hasRole(config('filament-shield.super_admin.name'))) {
            return false;
        }

        $activeSuperAdmins = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query
                ->where('name', config('filament-shield.super_admin.name')))
            ->count();

        return $activeSuperAdmins <= 1;
    }

    /**
     * Batasi data yang dapat dikelola:
     * - Non-super-admin tidak boleh melihat/mengelola akun super admin.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (! $user?->hasRole(config('filament-shield.super_admin.name'))) {
            $query->whereDoesntHave('roles', fn (Builder $query) => $query
                ->where('name', config('filament-shield.super_admin.name')));
        }

        return $query;
    }
}
