<?php

namespace Tests\Feature;

use App\Filament\Resources\AgendaResource;
use App\Filament\Resources\AgendaResource\Pages\CreateAgenda;
use App\Filament\Resources\AgendaResource\Pages\ListAgendas;
use App\Models\Agenda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AgendaResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_agenda_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAgenda::class)
            ->fillForm([
                'title' => 'Rapat Koordinasi',
                'date' => '2026-08-15',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'location' => 'Aula Disdikpora',
                'pic' => 'Kepala Dinas',
                'description' => 'Koordinasi bulanan',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $agenda = Agenda::where('title', 'Rapat Koordinasi')->firstOrFail();
        $this->assertEquals('2026-08-15', $agenda->date->format('Y-m-d'));
        $this->assertEquals('09:00', $agenda->start_time);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Agenda::class,
            'model_id' => $agenda->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_month_filter_shows_only_agendas_of_selected_month(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $inMonth = Agenda::create([
            'title' => 'Agenda Bulan Ini',
            'date' => '2026-08-10',
        ]);
        $otherMonth = Agenda::create([
            'title' => 'Agenda Bulan Lain',
            'date' => '2026-09-10',
        ]);

        Livewire::test(ListAgendas::class)
            ->filterTable('month', '2026-08')
            ->assertCanSeeTableRecords([$inMonth])
            ->assertCanNotSeeTableRecords([$otherMonth]);
    }

    public function test_past_agenda_is_considered_finished(): void
    {
        $past = Agenda::create([
            'title' => 'Agenda Lewat',
            'date' => now()->subDays(2)->format('Y-m-d'),
        ]);

        $today = Agenda::create([
            'title' => 'Agenda Hari Ini',
            'date' => now()->format('Y-m-d'),
        ]);

        $future = Agenda::create([
            'title' => 'Agenda Akan Datang',
            'date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $this->assertEquals('Selesai', $past->statusLabel());
        $this->assertEquals('Hari Ini', $today->statusLabel());
        $this->assertEquals('Akan Datang', $future->statusLabel());
    }

    public function test_redaksi_role_can_manage_agendas(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(AgendaResource::canViewAny());
        $this->assertTrue(AgendaResource::canCreate());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
