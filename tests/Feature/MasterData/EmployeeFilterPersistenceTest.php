<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use App\Enums\EmployeePosition;
use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeFilterPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->artisan('permissions:sync-tables');

        $superRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->superAdmin->assignRole($superRole);
    }

    /**
     * Paginator links preserve search and filter query parameters.
     */
    public function test_pagination_links_preserve_active_filter_query_strings(): void
    {
        Employee::factory()->count(25)->create([
            'position' => EmployeePosition::MeteringTechnician,
            'status' => EmployeeStatus::Active,
        ]);

        $queryParams = [
            'search' => 'Technician',
            'position' => EmployeePosition::MeteringTechnician->value,
            'status' => EmployeeStatus::Active->value,
        ];

        $response = $this->actingAs($this->superAdmin)->get(route('master-data.employees', $queryParams));

        $response->assertOk();

        // Check that page 2 link retains all query parameters
        $response->assertSee('search=Technician');
        $response->assertSee('position='.EmployeePosition::MeteringTechnician->value);
        $response->assertSee('status='.EmployeeStatus::Active->value);
    }

    /**
     * Storing an employee redirects preserving active query parameters.
     */
    public function test_store_employee_redirects_preserving_query_parameters(): void
    {
        $queryParams = [
            'page' => '2',
            'search' => 'John',
            'position' => EmployeePosition::MeteringEngineer->value,
        ];

        $payload = [
            'registration_number' => 'EMP-TEST-9999',
            'full_name' => 'John Persisted Doe',
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
            'join_date' => '2025-01-15',
            'phone' => '+213555123456',
            'address' => 'Hassi Messaoud',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('master-data.employees.store', $queryParams), $payload);

        $expectedUrl = route('master-data.employees', $queryParams);
        $response->assertRedirect($expectedUrl);
        $response->assertSessionHas('success', __('Employee created successfully.'));
    }

    /**
     * Updating an employee redirects preserving active query parameters.
     */
    public function test_update_employee_redirects_preserving_query_parameters(): void
    {
        $employee = Employee::factory()->create([
            'position' => EmployeePosition::MeteringEngineer,
            'status' => EmployeeStatus::Active,
        ]);

        $queryParams = [
            'page' => '3',
            'search' => 'Metering',
            'status' => EmployeeStatus::Active->value,
        ];

        $payload = [
            'registration_number' => $employee->registration_number,
            'full_name' => 'Updated Metering Engineer',
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
            'join_date' => '2024-05-10',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('master-data.employees.update', array_merge(['employee' => $employee->id], $queryParams)), $payload);

        $expectedUrl = route('master-data.employees', $queryParams);
        $response->assertRedirect($expectedUrl);
        $response->assertSessionHas('success', __('Employee updated successfully.'));
    }

    /**
     * Deleting an employee redirects preserving active query parameters.
     */
    public function test_destroy_employee_redirects_preserving_query_parameters(): void
    {
        $employee = Employee::factory()->create();

        $queryParams = [
            'page' => '1',
            'search' => 'DeleteMe',
            'position' => EmployeePosition::MeteringTechnician->value,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('master-data.employees.destroy', array_merge(['employee' => $employee->id], $queryParams)));

        $expectedUrl = route('master-data.employees', $queryParams);
        $response->assertRedirect($expectedUrl);
        $response->assertSessionHas('success', __('Employee deleted successfully.'));
    }
}
