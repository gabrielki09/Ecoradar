<?php

namespace Tests\Feature;

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Models\Collection\CollectionRequest;
use App\Models\General\City;
use App\Models\User;
use App\Services\Collection\CollectionRequestService;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;

class RewardConcurrencyTest extends TestCase
{
    public function test_two_completions_contending_for_the_same_row_award_one_pair(): void
    {
        $default = DB::getDefaultConnection();
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('O teste de bloqueio de linha requer PostgreSQL.');
        }

        // Os processos precisam enxergar os mesmos registros já confirmados.
        $schema = 'reward_test_'.bin2hex(random_bytes(8));
        $connection = [...config("database.connections.{$default}"), 'search_path' => $schema];
        DB::statement("create schema {$schema}");
        try {
            config(['database.connections.reward_race' => $connection]);
            DB::setDefaultConnection('reward_race');
            $priorMigrations = array_filter(
                glob(database_path('migrations/*.php')),
                static fn (string $file) => basename($file) < '2026_11_26_000000_create_reward_entries_table.php'
            );
            $this->artisan('migrate', ['--path' => $priorMigrations, '--realpath' => true])->assertExitCode(0);
            $owner = User::factory()->create(['profile_type' => 'common', 'document' => '10000000001', 'onboarding_completed_at' => now()]);
            $operator = User::factory()->create(['profile_type' => 'collector', 'document' => '10000000002', 'onboarding_completed_at' => now()]);
            $profile = $operator->collectorProfile()->create();
            $city = City::create(['codigo_tom' => 91001, 'codigo_ibge' => 9900001, 'municipio_tom' => 'Teste', 'municipio_ibge' => 'Teste', 'uf' => 'SC']);
            $request = CollectionRequest::create([
                'requester_user_id' => $owner->id, 'accepted_collector_profile_id' => $profile->id, 'status' => 'collected',
                'city_id' => $city->id, 'cep' => '89700000', 'neighborhood' => 'Centro', 'address' => 'Rua A',
            ]);
            $legacy = $request->replicate();
            $legacy->fill(['status' => 'completed', 'completion_date' => now()->subYear()])->save();
            $this->artisan('migrate')->assertExitCode(0);
            $this->assertDatabaseEmpty('reward_entries');

            $operatorId = $operator->id;
            $requestId = $request->id;
            $complete = static fn () => self::compete($connection, $operatorId, $requestId);

            $outcomes = Concurrency::driver('process')->run([$complete, $complete], 30);

            $this->assertEqualsCanonicalizing([200, 409], $outcomes);
            $this->assertDatabaseCount('reward_entries', 2);
            foreach ([$owner, $operator] as $user) {
                $this->assertDatabaseHas('reward_entries', ['collection_request_id' => $requestId, 'beneficiary_type' => 'user', 'beneficiary_id' => $user->id, 'points' => 10]);
            }
            $this->assertDatabaseCount('collection_histories', 1);
            $this->assertDatabaseHas('collection_requests', ['id' => $requestId, 'status' => 'completed']);
        } finally {
            DB::setDefaultConnection($default);
            DB::purge('reward_race');
            DB::statement("drop schema {$schema} cascade");
        }
    }

    public static function compete(array $connection, int $operatorId, int $requestId): int
    {
        config(['database.connections.reward_race' => $connection]);
        DB::setDefaultConnection('reward_race');
        CollectionRequest::retrieved(static function (CollectionRequest $request) {
            if ($request->status !== CollectionRequestStatus::COLLECTED) {
                return;
            }
            // Mantém a primeira transação aberta até o PostgreSQL confirmar a disputa real.
            $deadline = microtime(true) + 10;
            do {
                $waiting = DB::selectOne('select count(*) as n from pg_locks where not granted and pg_backend_pid() = any(pg_blocking_pids(pid))');
                if ($waiting->n > 0) {
                    return;
                }
                usleep(20000);
            } while (microtime(true) < $deadline);
            throw new RuntimeException('A segunda conclusão não aguardou o bloqueio da primeira.');
        });

        try {
            app(CollectionRequestService::class)->complete(User::findOrFail($operatorId), ['collection_request_id' => $requestId]);

            return 200;
        } catch (ConflictHttpException $exception) {
            return $exception->getStatusCode();
        }
    }
}
