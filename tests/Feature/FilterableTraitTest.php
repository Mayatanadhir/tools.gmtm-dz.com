<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FilterableTraitTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_filter_by_exact_attribute(): void
    {
        User::factory()->create(['email' => 'alice@example.com', 'name' => 'Alice']);
        User::factory()->create(['email' => 'bob@example.com', 'name' => 'Bob']);

        $results = User::filter(['email' => 'alice@example.com'])->get();

        $this->assertCount(1, $results);
        $this->assertSame('Alice', $results->first()->name);
    }

    public function test_filter_by_search_keyword_across_searchable_columns(): void
    {
        User::factory()->create(['name' => 'Acrobat Developer', 'email' => 'dev1@example.com']);
        User::factory()->create(['name' => 'Backend Engineer', 'email' => 'acrobat@company.com']);
        User::factory()->create(['name' => 'Charlie Chaplin', 'email' => 'charlie@example.com']);

        $results = User::filter(['search' => 'acrobat'])->get();

        $this->assertCount(2, $results);
        $emails = $results->pluck('email')->all();
        $this->assertContains('dev1@example.com', $emails);
        $this->assertContains('acrobat@company.com', $emails);
    }

    public function test_filter_by_array_values_using_where_in(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $u3 = User::factory()->create();

        $results = User::filter(['id' => [$u1->id, $u3->id]])->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($u1));
        $this->assertTrue($results->contains($u3));
        $this->assertFalse($results->contains($u2));
    }

    public function test_filter_with_date_range_conditions(): void
    {
        $oldUser = User::factory()->create(['created_at' => now()->subDays(10)]);
        $recentUser = User::factory()->create(['created_at' => now()->subDays(2)]);

        $results = User::filter([
            'created_at' => [
                'from' => now()->subDays(5)->toDateTimeString(),
            ],
        ])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($recentUser));
    }

    public function test_filter_applies_dynamic_sorting(): void
    {
        User::factory()->create(['name' => 'Zara']);
        User::factory()->create(['name' => 'Adam']);

        $resultsDesc = User::filter(['sort_by' => 'name', 'sort_direction' => 'desc'])->get();
        $this->assertSame('Zara', $resultsDesc->first()->name);

        $resultsAsc = User::filter(['sort_by' => 'name', 'sort_direction' => 'asc'])->get();
        $this->assertSame('Adam', $resultsAsc->first()->name);
    }

    public function test_unwhitelisted_attributes_are_safely_ignored(): void
    {
        $user = User::factory()->create(['password' => 'secret-hashed']);

        // Attempt to query by unwhitelisted attribute 'password'
        $results = User::filter(['password' => 'secret-hashed'])->get();

        // The filter should ignore 'password' and return all users rather than filtering or erroring
        $this->assertGreaterThanOrEqual(1, $results->count());
    }

    public function test_custom_filter_hook_executes_on_model(): void
    {
        $admin = User::factory()->create(['name' => 'Admin User']);
        $admin->assignRole('Admin');

        $regular = User::factory()->create(['name' => 'Regular User']);
        $regular->assignRole('User');

        $results = User::filter(['role' => 'Admin'])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($admin));
    }

    public function test_filter_accepts_request_instance(): void
    {
        User::factory()->create(['email' => 'requested@example.com']);

        $request = new Request(['email' => 'requested@example.com']);

        $results = User::filter($request)->get();

        $this->assertCount(1, $results);
        $this->assertSame('requested@example.com', $results->first()->email);
    }

    public function test_base_repository_filter_and_paginate_with_filter(): void
    {
        User::factory()->create(['name' => 'Alpha Tester', 'email' => 'alpha@test.com']);
        User::factory()->create(['name' => 'Beta Tester', 'email' => 'beta@test.com']);

        $collection = $this->userRepository->filter(['name' => 'Alpha Tester']);
        $this->assertCount(1, $collection);
        $this->assertSame('alpha@test.com', $collection->first()->email);

        $paginator = $this->userRepository->paginateWithFilter(['search' => 'Tester'], 10);
        $this->assertSame(2, $paginator->total());
    }
}
