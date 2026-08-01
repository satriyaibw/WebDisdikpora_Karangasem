<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    /**
     * Tampilkan rincian nilai lama/baru dalam format terbaca.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Umum')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Pelaku')
                            ->placeholder('Sistem / CLI'),
                        TextEntry::make('action')
                            ->label('Aksi')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'create' => 'success',
                                'update' => 'warning',
                                'delete' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => (new \App\Models\AuditLog(['action' => $state]))->action_label),
                        TextEntry::make('model_type')
                            ->label('Model')
                            ->formatStateUsing(fn (string $state): string => class_basename($state)),
                        TextEntry::make('model_id')
                            ->label('ID Data'),
                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->placeholder('-'),
                        TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Waktu')
                            ->dateTime('d M Y, H:i:s'),
                    ])
                    ->columns(2),
                Section::make('Nilai Lama')
                    ->visible(fn (AuditLog $record): bool => filled($record->old_values))
                    ->schema([
                        KeyValueEntry::make('old_values')
                            ->label('Perubahan Sebelumnya'),
                    ]),
                Section::make('Nilai Baru')
                    ->visible(fn (AuditLog $record): bool => filled($record->new_values))
                    ->schema([
                        KeyValueEntry::make('new_values')
                            ->label('Perubahan Sesudahnya'),
                    ]),
            ]);
    }
}
