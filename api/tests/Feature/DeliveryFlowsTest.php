<?php

namespace Tests\Feature;

use App\Enum\Collection\CollectionItemUnit;
use App\Enum\ProfileTypes;
use App\Models\Collection\CollectionRequest;
use App\Models\Company\Company;
use App\Models\General\City;
use App\Models\User;
use App\Models\WasteCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryFlowsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:') {
            $this->artisan('migrate')->assertExitCode(0);
        }
    }

    public function test_direct_collection_details_respect_assignment_and_expose_eligible_actions(): void
    {
        $owner = $this->user(ProfileTypes::COMMON, '11111111111');
        $other = $this->user(ProfileTypes::COMMON, '22222222222');
        $collector = $this->user(ProfileTypes::COLLECTOR, '33333333333');
        $profile = $collector->collectorProfile()->create(['service_description' => 'Coleta para QA']);
        $city = $this->city();
        $category = WasteCategory::create(['name' => 'Material QA', 'is_system' => true]);
        $profile->cities()->create(['city_id' => $city->id]);
        $profile->wasteCategories()->create(['waste_category_id' => $category->id]);

        Sanctum::actingAs($owner);
        $id = $this->postJson('/api/v1/collection-requests', [
            'city_id' => $city->id,
            'cep' => '89700000',
            'neighborhood' => 'Centro',
            'address' => 'Rua privada, 10',
            'items' => [['waste_category_id' => $category->id, 'approximate_quantity' => 1, 'quantity_unit' => 'UN']],
        ])->assertOk()->json('data.collection_request.id');

        Sanctum::actingAs($other);
        $this->getJson("/api/v1/collection-requests/{$id}")->assertForbidden();

        Sanctum::actingAs($collector);
        $this->getJson('/api/v1/collection-requests/all')
            ->assertOk()
            ->assertJsonMissingPath('data.collection_requests.data.0.address');
        $this->getJson("/api/v1/collection-requests/{$id}")
            ->assertOk()
            ->assertJsonPath('data.collection_request.acceptance_block_reason', null)
            ->assertJsonPath('data.collection_request.assigned_to_provider', false)
            ->assertJsonMissingPath('data.collection_request.address');

        $this->postJson('/api/v1/collection-requests/accept', ['collection_request_id' => $id])->assertOk();
        $this->getJson("/api/v1/collection-requests/{$id}")
            ->assertOk()
            ->assertJsonPath('data.collection_request.assigned_to_provider', true)
            ->assertJsonPath('data.collection_request.address', 'Rua privada, 10');
    }

    public function test_collection_items_require_a_supported_unit_and_keep_each_selection(): void
    {
        $owner = $this->user(ProfileTypes::COMMON, '88888888888');
        Sanctum::actingAs($owner);
        $items = [];
        foreach (CollectionItemUnit::cases() as $unit) {
            $category = WasteCategory::create(['name' => 'Material '.$unit->value, 'is_system' => true]);
            $items[] = ['waste_category_id' => $category->id, 'approximate_quantity' => 1.25, 'quantity_unit' => $unit->value];
        }
        $url = '/api/v1/collection-requests';
        $payload = ['city_id' => $this->city()->id, 'cep' => '89700000', 'neighborhood' => 'Centro', 'address' => 'Rua A'];
        $invalid = $items;
        unset($invalid[0]['quantity_unit']);
        $invalid[1]['quantity_unit'] = 'INVÁLIDA';
        $this->postJson($url, [...$payload, 'items' => $invalid])->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.quantity_unit', 'items.1.quantity_unit']);
        $this->assertDatabaseMissing('collection_requests', ['requester_user_id' => $owner->id]);

        $id = $this->postJson($url, [...$payload, 'items' => $items])->assertOk()->json('data.collection_request.id');
        $detailItems = $this->getJson("{$url}/{$id}")->assertOk()->json('data.collection_request.items');
        $byCategory = array_column($detailItems, null, 'waste_category_id');
        foreach ($items as $item) {
            $stored = $byCategory[$item['waste_category_id']];
            $this->assertSame($item['quantity_unit'], $stored['quantity_unit']);
            $this->assertSame('1.25', $stored['approximate_quantity']);
        }
    }

    public function test_legacy_item_keeps_its_quantity_without_inferring_a_unit(): void
    {
        $owner = $this->user(ProfileTypes::COMMON, '99999999999');
        $request = CollectionRequest::create([
            'requester_user_id' => $owner->id, 'city_id' => $this->city()->id,
            'cep' => '89700000', 'neighborhood' => 'Centro', 'address' => 'Rua A',
        ]);
        $category = WasteCategory::create(['name' => 'Vidro QA', 'is_system' => true]);
        $request->items()->create(['waste_category_id' => $category->id, 'approximate_quantity' => 2.5]);

        Sanctum::actingAs($owner);
        $this->getJson("/api/v1/collection-requests/{$request->id}")->assertOk()
            ->assertJsonPath('data.collection_request.items.0.approximate_quantity', '2.50')
            ->assertJsonStructure(['data' => ['collection_request' => ['items' => [['quantity_unit']]]]])
            ->assertJsonPath('data.collection_request.items.0.quantity_unit', null);
    }

    public function test_only_the_responsible_company_user_can_change_coverage(): void
    {
        $responsible = $this->user(ProfileTypes::COMPANY, '44444444444');
        $member = $this->user(ProfileTypes::COMPANY, '55555555555');
        $company = Company::create([
            'document' => '00000000000000',
            'company_name' => 'Empresa teste',
            'responsible' => 'Responsável QA',
        ]);
        $responsible->companies()->attach($company, ['role' => 'responsible']);
        $member->companies()->attach($company, ['role' => 'member']);
        $city = $this->city();
        $first = WasteCategory::create(['name' => 'Material A QA', 'is_system' => true]);
        $second = WasteCategory::create(['name' => 'Material B QA', 'is_system' => true]);
        $url = "/api/v1/company-profile/{$company->id}";
        $payload = [
            'company_name' => 'Empresa teste',
            'responsible' => 'Responsável QA',
            'cities' => [$city->id],
            'waste_categories' => [$first->id],
        ];

        Sanctum::actingAs($member);
        $this->putJson($url, $payload)->assertNotFound();
        Sanctum::actingAs($responsible);
        $this->putJson($url, $payload)->assertOk();
        $this->putJson($url, [...$payload, 'waste_categories' => [$second->id]])->assertOk();
        $this->putJson($url, $payload)
            ->assertOk()
            ->assertJsonCount(1, 'data.company.waste_categories')
            ->assertJsonPath('data.company.waste_categories.0.waste_category_id', $first->id);
    }

    public function test_point_creation_checks_profile_before_fields_and_rejects_inverted_hours(): void
    {
        $common = $this->user(ProfileTypes::COMMON, '66666666666');
        $collector = $this->user(ProfileTypes::COLLECTOR, '77777777777');
        $collector->collectorProfile()->create(['service_description' => 'Coleta para QA']);
        $category = WasteCategory::create(['name' => 'Material aceito QA', 'is_system' => true]);

        Sanctum::actingAs($common);
        $this->postJson('/api/v1/collection-points', [])->assertForbidden();

        Sanctum::actingAs($collector);
        $this->postJson('/api/v1/collection-points', [
            'name' => 'Ponto QA',
            'cep' => '89700000',
            'address' => 'Rua Teste',
            'neighborhood' => 'Centro',
            'guidelines' => 'Trazer material limpo',
            'contact' => 'qa@example.test',
            'opening_hours' => [['opening_day' => 1, 'opens_at' => '12:00', 'closes_at' => '08:00']],
            'waste_categories' => [['waste_category_id' => $category->id]],
        ])->assertUnprocessable()->assertJsonValidationErrors('opening_hours.0.closes_at');
    }

    private function user(ProfileTypes $profile, string $document): User
    {
        return User::factory()->create([
            'document' => $document,
            'profile_type' => $profile,
            'onboarding_completed_at' => now(),
        ]);
    }

    private function city(): City
    {
        return City::create([
            'codigo_tom' => 91001,
            'codigo_ibge' => 9900001,
            'municipio_tom' => 'Cidade QA',
            'municipio_ibge' => 'Cidade QA',
            'uf' => 'SC',
        ]);
    }
}
