<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Settings;
use Filament\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Pengaturan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan';

    protected static ?string $slug = 'pengaturan';

    protected static ?string $navigationGroup = 'Profil';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.pengaturan';

    public ?array $data = [];

    /**
     * Hanya user dengan permission setting.read yang dapat membuka halaman.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->can('setting.read') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'site' => [
                'name' => settings('site.name', ''),
                'short_name' => settings('site.short_name', ''),
                'tagline' => settings('site.tagline', ''),
                'address' => settings('site.address', ''),
                'email' => settings('site.email', ''),
                'phone' => settings('site.phone', ''),
            ],
            'profile' => [
                'kadis_name' => settings('profile.kadis_name', ''),
                'sekretariat_name' => settings('profile.sekretariat_name', ''),
                'welcome' => settings('profile.welcome', ''),
                'vision' => settings('profile.vision', ''),
                'mission' => settings('profile.mission', ''),
                'duties' => settings('profile.duties', ''),
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Situs')
                    ->description('Nama instansi yang tampil di header, footer, dan judul portal.')
                    ->schema([
                        TextInput::make('site.name')
                            ->label('Nama Instansi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('site.short_name')
                            ->label('Nama Singkat')
                            ->maxLength(255),
                        TextInput::make('site.tagline')
                            ->label('Tagline')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Kontak')
                    ->description('Informasi kontak di halaman Kontak dan footer.')
                    ->schema([
                        TextInput::make('site.address')
                            ->label('Alamat')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('site.email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('site.phone')
                            ->label('Telepon')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Profil Instansi')
                    ->description('Konten halaman Profil Instansi di portal publik. Kosongkan untuk memakai teks bawaan.')
                    ->schema([
                        TextInput::make('profile.kadis_name')
                            ->label('Nama Kepala Dinas')
                            ->maxLength(255),
                        TextInput::make('profile.sekretariat_name')
                            ->label('Nama Sekretaris Dinas')
                            ->maxLength(255),
                        RichEditor::make('profile.welcome')
                            ->label('Sambutan Kepala Dinas')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike', 'link',
                                'orderedList', 'bulletList', 'blockquote', 'h2', 'h3', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                        RichEditor::make('profile.vision')
                            ->label('Visi')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'link',
                                'orderedList', 'bulletList', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                        RichEditor::make('profile.mission')
                            ->label('Misi')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'link',
                                'orderedList', 'bulletList', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                        RichEditor::make('profile.duties')
                            ->label('Tugas & Fungsi')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'link',
                                'orderedList', 'bulletList', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Simpan seluruh setting dari form lalu invalidasi cache publik.
     */
    public function save(): void
    {
        $data = \Illuminate\Support\Arr::dot($this->form->getState());

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $this->groupFor($key)]
            );
        }

        Settings::flush();

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon('heroicon-o-check')
                ->submit('form'),
        ];
    }

    /**
     * Grup setting sesuai awalan key (dipakai kolom `group`).
     */
    private function groupFor(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'profile.') => 'profile',
            str_starts_with($key, 'site.address') => 'contact',
            str_starts_with($key, 'site.email') => 'contact',
            str_starts_with($key, 'site.phone') => 'contact',
            default => 'general',
        };
    }
}
