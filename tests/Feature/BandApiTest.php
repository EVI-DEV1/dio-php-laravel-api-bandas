<?php

namespace Tests\Feature;

use App\Jobs\RecordBandCreated;
use App\Models\Band;
use Database\Seeders\BandSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BandApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BandSeeder::class);
    }

    public function test_lists_the_seeded_bands_with_the_original_contract(): void
    {
        $this->getJson('/api/bands')
            ->assertOk()
            ->assertJsonCount(5)
            ->assertJsonPath('0.name', 'Dream Theater')
            ->assertExactJsonStructure(['*' => ['id', 'name', 'gender']]);
    }

    public function test_finds_a_band_by_id(): void
    {
        $this->getJson('/api/bands/3')
            ->assertOk()
            ->assertExactJson(['id' => 3, 'name' => 'Dio', 'gender' => 'heavy metal']);
    }

    public function test_returns_json_not_found_for_unknown_or_non_numeric_identifiers(): void
    {
        $this->getJson('/api/bands/999999')->assertNotFound();
        $this->getJson('/api/bands/not-a-number')->assertNotFound();
    }

    public function test_filters_by_musical_gender_without_case_sensitivity(): void
    {
        $this->getJson('/api/bands/gender/HEAVY%20METAL')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.name', 'Dio')
            ->assertJsonPath('1.name', 'Metallica');
    }

    public function test_an_unknown_gender_returns_an_empty_collection(): void
    {
        $this->getJson('/api/bands/gender/jazz')->assertOk()->assertExactJson([]);
    }

    public function test_creates_and_persists_only_validated_fields_and_queues_an_event(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/bands/store', [
            'id' => 9999,
            'name' => '  Angra  ',
            'gender' => '  POWER METAL  ',
            'is_admin' => true,
        ])->assertCreated()->assertJsonPath('name', 'Angra')
            ->assertJsonPath('gender', 'power metal')
            ->assertJsonMissingPath('is_admin');

        $bandId = $response->json('id');

        $this->assertNotSame(9999, $bandId);
        $response->assertHeader('Location', url('/api/bands/'.$bandId));
        $this->assertDatabaseHas('bands', ['id' => $bandId, 'name' => 'Angra']);
        $this->getJson('/api/bands/'.$bandId)->assertOk()->assertJsonPath('name', 'Angra');
        Queue::assertPushed(RecordBandCreated::class, fn (RecordBandCreated $job): bool => $job->bandId === $bandId);
    }

    public function test_missing_fields_do_not_create_a_band_or_a_job(): void
    {
        Queue::fake();

        $this->postJson('/api/bands/store', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'gender']);

        $this->assertDatabaseCount('bands', 5);
        Queue::assertNothingPushed();
    }

    public function test_rejects_invalid_types_and_field_lengths(): void
    {
        Queue::fake();

        foreach ([
            ['name' => 'ab', 'gender' => 'rock'],
            ['name' => str_repeat('a', 121), 'gender' => 'rock'],
            ['name' => ['invalid'], 'gender' => 'rock'],
            ['name' => 'Banda', 'gender' => str_repeat('a', 81)],
        ] as $payload) {
            $this->postJson('/api/bands/store', $payload)->assertUnprocessable();
        }

        $this->assertDatabaseCount('bands', 5);
        Queue::assertNothingPushed();
    }

    public function test_the_queued_job_is_idempotent_when_retried(): void
    {
        $band = Band::factory()->create();
        $job = new RecordBandCreated($band->id);

        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('band_creation_logs', 1);
        $this->assertDatabaseHas('band_creation_logs', ['band_id' => $band->id]);
    }

    public function test_a_database_worker_processes_the_creation_event(): void
    {
        config(['queue.default' => 'database']);

        $response = $this->postJson('/api/bands/store', [
            'name' => 'Sepultura', 'gender' => 'thrash metal',
        ])->assertCreated();

        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('band_creation_logs', 0);

        $this->artisan('queue:work', ['--once' => true, '--stop-when-empty' => true])
            ->assertExitCode(0);

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseHas('band_creation_logs', ['band_id' => $response->json('id')]);
    }

    public function test_seed_can_be_rerun_without_duplicating_data(): void
    {
        $this->seed(BandSeeder::class);

        $this->assertDatabaseCount('bands', 5);
    }
}
