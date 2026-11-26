<?php

namespace Tests\Feature;

use App\Enum\ProfileTypes;
use App\Models\Collection\CollectionRequest;
use App\Models\Collection\RewardEntry;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Models\General\City;
use App\Models\User;
use App\Models\WasteCategory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class RewardPointsTest extends TestCase
{
    use DatabaseTransactions;

    private const REWARDS = '/api/v1/reward-points';

    private const REQUESTS = '/api/v1/collection-requests';

    private User $owner;

    private User $collector;

    private CollectorProfile $profile;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();
        if (config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:') {
            $this->artisan('migrate')->assertExitCode(0);
        }
        $this->owner = $this->user(ProfileTypes::COMMON);
        $this->collector = $this->user(ProfileTypes::COLLECTOR);
        $this->profile = $this->collector->collectorProfile()->create();
        $this->city = City::create(['codigo_tom' => 90001, 'codigo_ibge' => 9900001, 'municipio_tom' => 'Teste', 'municipio_ibge' => 'Teste', 'uf' => 'SC']);
    }

    public function test_only_completion_credits_both_accounts_once(): void
    {
        $category = WasteCategory::create(['name' => 'Papel QA', 'is_system' => true]);
        $this->profile->cities()->create(['city_id' => $this->city->id]);
        $this->profile->wasteCategories()->create(['waste_category_id' => $category->id]);
        Sanctum::actingAs($this->owner);
        $id = $this->postJson(self::REQUESTS, [
            'city_id' => $this->city->id, 'cep' => '89700000', 'neighborhood' => 'Centro', 'address' => 'Rua A',
            'items' => [['waste_category_id' => $category->id, 'approximate_quantity' => 2, 'quantity_unit' => 'KG']],
        ])->assertOk()->json('data.collection_request.id');
        $payload = ['collection_request_id' => $id];
        $this->getJson(self::REQUESTS.'/mine')->assertOk();
        $this->getJson(self::REQUESTS."/{$id}")->assertOk();
        $this->postJson(self::REQUESTS.'/complete', $payload)->assertForbidden();
        Sanctum::actingAs($this->collector);
        $this->postJson(self::REQUESTS.'/complete', $payload)->assertConflict();
        $this->postJson(self::REQUESTS.'/accept', $payload)->assertOk();
        $this->postJson(self::REQUESTS.'/complete', $payload)->assertConflict();
        $this->postJson(self::REQUESTS.'/collected', $payload)->assertOk();
        $this->assertDatabaseMissing('reward_entries', ['collection_request_id' => $id]);

        $this->postJson(self::REQUESTS.'/complete', [...$payload, 'points' => 999, 'beneficiary_id' => 999])
            ->assertOk()->assertJsonPath('data.points_awarded', 10)->assertJsonPath('data.collection_request.status', 'completed');
        $this->postJson(self::REQUESTS.'/complete', $payload)->assertConflict();
        $this->assertSame(2, RewardEntry::where('collection_request_id', $id)->count());
        foreach ([$this->collector, $this->owner] as $user) {
            Sanctum::actingAs($user);
            $this->getJson(self::REWARDS)->assertOk()->assertJsonPath('data.balance', 10);
            $this->getJson(self::REWARDS.'/history')->assertOk()
                ->assertJsonPath('data.history.total', 1)
                ->assertJsonPath('data.history.data.0.points', 10)
                ->assertJsonPath('data.history.data.0.collection_request_id', $id)
                ->assertJsonPath('data.history.data.0.reason', 'collection_completion')
                ->assertJsonPath('data.history.data.0.can_view_collection', true)
                ->assertJsonPath('data.history.data.0.description', $user->is($this->owner) ? 'Coleta da sua solicitação concluída' : 'Recompensa por coleta realizada')
                ->assertJsonStructure(['data' => ['history' => ['data' => [['created_at']]]]])
                ->assertJsonMissingPath('data.history.data.0.beneficiary_id');
        }
        $this->postJson(self::REQUESTS.'/cancel', [...$payload, 'cancellation_reason' => 'Teste'])->assertConflict();
    }

    public function test_company_owns_the_credit_and_only_linked_accounts_can_read_it(): void
    {
        $member = $this->user(ProfileTypes::COMPANY);
        $manager = $this->user(ProfileTypes::COMPANY);
        $company = $this->company();
        $other = $this->company();
        $member->companies()->attach($company, ['role' => 'member']);
        $manager->companies()->attach($company, ['role' => 'responsible']);
        $manager->companies()->attach($other, ['role' => 'responsible']);
        $request = $this->collection(['accepted_company_id' => $company->id, 'accepted_collector_profile_id' => null]);
        Sanctum::actingAs($manager);
        $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $request->id, 'company_id' => $other->id])->assertForbidden();
        Sanctum::actingAs($member);
        $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $request->id, 'company_id' => $company->id])
            ->assertOk()->assertJsonPath('message', 'Coleta concluída! A empresa recebeu 10 pontos de recompensa.');
        $this->assertSame(2, RewardEntry::where('collection_request_id', $request->id)->count());
        $this->assertDatabaseHas('reward_entries', ['collection_request_id' => $request->id, 'beneficiary_type' => 'company', 'beneficiary_id' => $company->id, 'points' => 10]);
        $this->assertDatabaseMissing('reward_entries', ['collection_request_id' => $request->id, 'beneficiary_type' => 'user', 'beneficiary_id' => $member->id]);
        foreach ([self::REWARDS, self::REWARDS.'/history'] as $path) {
            $this->getJson($path)->assertUnprocessable();
            $this->getJson($path.'?company_id='.$other->id)->assertNotFound();
        }
        foreach ([$member, $manager] as $reader) {
            Sanctum::actingAs($reader);
            $this->getJson(self::REWARDS.'?company_id='.$company->id)->assertOk()->assertJsonPath('data.balance', 10);
            $this->getJson(self::REWARDS.'/history?company_id='.$company->id)->assertOk()
                ->assertJsonPath('data.history.data.0.collection_request_id', $request->id)
                ->assertJsonPath('data.history.data.0.description', 'Recompensa por coleta realizada');
        }
        $this->getJson(self::REWARDS.'?company_id='.$other->id)->assertOk()->assertJsonPath('data.balance', 0);
        $this->getJson(self::REWARDS.'/history?company_id='.$other->id)->assertOk()->assertJsonCount(0, 'data.history.data');
        Sanctum::actingAs($this->owner);
        $this->getJson(self::REWARDS)->assertOk()->assertJsonPath('data.balance', 10);
    }

    public function test_balance_and_paginated_history_keep_the_points_awarded_at_completion(): void
    {
        Sanctum::actingAs($this->collector);
        $first = $this->collection();
        $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $first->id])->assertOk();
        config(['rewards.collection_completion_points' => 14]);
        $second = $this->collection();
        $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $second->id])->assertOk()->assertJsonPath('data.points_awarded', 14);
        Sanctum::actingAs($this->owner);
        $this->getJson(self::REWARDS)->assertOk()->assertJsonPath('data.balance', 24);
        $this->getJson(self::REWARDS.'/history?per_page=1')->assertOk()->assertJsonPath('data.history.last_page', 2)
            ->assertJsonPath('data.history.data.0.collection_request_id', $second->id)->assertJsonPath('data.history.data.0.points', 14);
        $this->getJson(self::REWARDS.'/history?page=2&per_page=1')->assertOk()->assertJsonCount(1, 'data.history.data')
            ->assertJsonPath('data.history.data.0.collection_request_id', $first->id)->assertJsonPath('data.history.data.0.points', 10);
        $this->getJson(self::REWARDS.'/history?per_page=101')->assertUnprocessable();
        Sanctum::actingAs($this->user(ProfileTypes::COMMON));
        $this->getJson(self::REWARDS.'?user_id='.$this->owner->id)->assertOk()->assertJsonPath('data.balance', 0);
        $this->getJson(self::REWARDS.'/history?beneficiary_id='.$this->owner->id)->assertOk()->assertJsonCount(0, 'data.history.data');
        $this->getJson(self::REWARDS.'?company_id=1')->assertUnprocessable();
        $this->postJson(self::REWARDS, ['points' => 100])->assertMethodNotAllowed();
    }

    public function test_second_credit_failure_rolls_back_the_first_credit_status_date_and_history(): void
    {
        $request = $this->collection();
        $secondAttempted = false;
        RewardEntry::creating(function (RewardEntry $entry) use (&$secondAttempted) {
            if ($entry->beneficiary_id === $this->collector->id) {
                $secondAttempted = true;
                $this->assertDatabaseHas('reward_entries', ['collection_request_id' => $entry->collection_request_id, 'beneficiary_id' => $this->owner->id]);
                throw new RuntimeException('Falha simulada no segundo lançamento.');
            }
        });
        Sanctum::actingAs($this->collector);
        $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $request->id])->assertInternalServerError();
        $this->assertTrue($secondAttempted);
        $this->assertDatabaseMissing('reward_entries', ['collection_request_id' => $request->id]);
        $this->assertDatabaseMissing('collection_histories', ['collection_request_id' => $request->id, 'new_status' => 'completed']);
        $this->assertDatabaseHas('collection_requests', ['id' => $request->id, 'status' => 'collected', 'completion_date' => null]);
    }

    public function test_database_rejects_a_duplicate_completion_credit(): void
    {
        $request = $this->collection();
        Sanctum::actingAs($this->collector);
        $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $request->id])->assertOk();
        $this->expectException(UniqueConstraintViolationException::class);
        RewardEntry::where('collection_request_id', $request->id)->firstOrFail()->replicate()->save();
    }

    public function test_cancelled_and_previously_completed_collections_never_receive_credits(): void
    {
        $legacy = $this->collection(['status' => 'completed', 'completion_date' => now()->subYear()]);
        $open = $this->collection(['status' => 'open', 'accepted_collector_profile_id' => null]);
        Sanctum::actingAs($this->owner);
        $this->postJson(self::REQUESTS.'/cancel', ['collection_request_id' => $open->id, 'cancellation_reason' => 'Mudança de planos'])->assertOk();
        $this->getJson(self::REQUESTS."/{$legacy->id}")->assertOk();
        $this->getJson(self::REQUESTS.'/mine')->assertOk();
        Sanctum::actingAs($this->collector);
        foreach ([$open, $legacy] as $request) {
            $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $request->id])->assertConflict();
            $this->assertDatabaseMissing('reward_entries', ['collection_request_id' => $request->id]);
        }
        $this->getJson(self::REWARDS)->assertOk()->assertJsonPath('data.balance', 0);
        $this->getJson(self::REWARDS.'/history')->assertOk()->assertJsonCount(0, 'data.history.data');
    }

    public function test_authentication_assignment_and_onboarding_are_required(): void
    {
        $request = $this->collection();
        $payload = ['collection_request_id' => $request->id];
        $this->getJson(self::REWARDS)->assertUnauthorized();
        $this->getJson(self::REWARDS.'/history')->assertUnauthorized();
        $other = $this->user(ProfileTypes::COLLECTOR);
        $other->collectorProfile()->create();
        Sanctum::actingAs($other);
        $this->postJson(self::REQUESTS.'/complete', $payload)->assertForbidden();
        foreach ([$this->collector, $this->user(ProfileTypes::COMPANY)] as $user) {
            $user->update(['onboarding_completed_at' => null]);
            Sanctum::actingAs($user);
            $this->getJson(self::REWARDS)->assertForbidden();
            $this->getJson(self::REWARDS.'/history')->assertForbidden();
            $this->postJson(self::REQUESTS.'/complete', $payload)->assertForbidden();
        }
        $this->assertDatabaseMissing('reward_entries', ['collection_request_id' => $request->id]);
    }

    public function test_conflicting_participants_prevent_completion(): void
    {
        Sanctum::actingAs($this->collector);
        foreach ([['requester_user_id' => $this->collector->id], ['accepted_company_id' => $this->company()->id]] as $invalid) {
            $request = $this->collection($invalid);
            $this->postJson(self::REQUESTS.'/complete', ['collection_request_id' => $request->id])->assertConflict();
            $this->assertDatabaseHas('collection_requests', ['id' => $request->id, 'status' => 'collected']);
            $this->assertDatabaseMissing('reward_entries', ['collection_request_id' => $request->id]);
        }
    }

    private function user(ProfileTypes $type): User
    {
        return User::factory()->create(['document' => fake()->unique()->numerify('###########'), 'profile_type' => $type, 'onboarding_completed_at' => now()]);
    }

    private function company(): Company
    {
        return Company::create(['document' => fake()->unique()->numerify('##############'), 'company_name' => 'Eco QA', 'responsible' => 'Responsável QA']);
    }

    private function collection(array $overrides = []): CollectionRequest
    {
        return CollectionRequest::create([
            'requester_user_id' => $this->owner->id, 'accepted_collector_profile_id' => $this->profile->id, 'status' => 'collected',
            'city_id' => $this->city->id, 'cep' => '89700000', 'neighborhood' => 'Centro', 'address' => 'Rua A',
            ...$overrides,
        ]);
    }
}
