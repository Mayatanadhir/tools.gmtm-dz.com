<?php

declare(strict_types=1);

namespace Tests\Feature\MasterData;

use App\Enums\EmployeePosition;
use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $standardUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->artisan('permissions:sync-tables');

        $superRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->superAdmin->assignRole($superRole);

        $this->standardUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->standardUser->assignRole($userRole);
    }

    /**
     * 1. Guests are redirected to the login page.
     */
    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('master-data.employees'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. User without permission cannot view employees (403 Forbidden).
     */
    public function test_user_without_permission_cannot_view_employees(): void
    {
        $response = $this->actingAs($this->standardUser)->get(route('master-data.employees'));

        $response->assertForbidden();
    }

    /**
     * 3. User with permission can view employees list.
     */
    public function test_user_with_permission_can_view_employees_list(): void
    {
        $this->standardUser->givePermissionTo('view employees');

        $employee = Employee::factory()->create([
            'full_name' => 'MAYATA Nadhir',
            'registration_number' => 'EMP-001',
        ]);

        $response = $this->actingAs($this->standardUser)->get(route('master-data.employees'));

        $response->assertOk();
        $response->assertSee('MAYATA Nadhir');
        $response->assertSee('EMP-001');
    }

    /**
     * 4. Financial compensation is masked without specific permission.
     */
    public function test_financial_compensation_is_masked_without_specific_permission(): void
    {
        $this->standardUser->givePermissionTo('view employees');

        Employee::factory()->create([
            'full_name' => 'Secret Earner',
            'salary' => 99999.00,
            'daily_rate' => 12345.00,
        ]);

        $response = $this->actingAs($this->standardUser)->get(route('master-data.employees'));

        $response->assertOk();
        $response->assertSee('•••••••• DZD');
        $response->assertDontSee('99,999.00 DZD');
        $response->assertDontSee('12,345.00 DZD');
    }

    /**
     * 5. User with compensation permission can view financial data.
     */
    public function test_user_with_compensation_permission_can_view_financial_data(): void
    {
        $this->standardUser->givePermissionTo(['view employees', 'view employee compensation']);

        Employee::factory()->create([
            'full_name' => 'Visible Earner',
            'salary' => 85000.00,
            'daily_rate' => 10000.00,
        ]);

        $response = $this->actingAs($this->standardUser)->get(route('master-data.employees'));

        $response->assertOk();
        $response->assertSee('85,000.00 DZD');
        $response->assertSee('10,000.00 DZD');
        $response->assertDontSee('•••••••• DZD');
    }

    /**
     * 6. Super Admin bypasses all employee permissions.
     */
    public function test_super_admin_bypasses_all_employee_permissions(): void
    {
        Employee::factory()->create([
            'full_name' => 'Admin Viewable',
            'salary' => 77000.00,
            'daily_rate' => 9000.00,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('master-data.employees'));

        $response->assertOk();
        $response->assertSee('Admin Viewable');
        $response->assertSee('77,000.00 DZD');
    }

    /**
     * 7. Authorized user can create employee with photo and WebP conversion.
     */
    public function test_authorized_user_can_create_employee_with_photo_and_webp_conversion(): void
    {
        $this->standardUser->givePermissionTo(['view employees', 'create employees']);

        $photo = UploadedFile::fake()->image('staff.jpg', 600, 600);

        $response = $this->actingAs($this->standardUser)->post(route('master-data.employees.store'), [
            'full_name' => 'New Engineer',
            'registration_number' => 'ENG-999',
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
            'join_date' => '2026-09-01',
            'salary' => 70000.00,
            'daily_rate' => 8000.00,
            'address' => 'Algiers Center',
            'photo' => $photo,
        ]);

        $response->assertRedirect(route('master-data.employees'));

        $this->assertDatabaseHas('employees', [
            'full_name' => 'New Engineer',
            'registration_number' => 'ENG-999',
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
        ]);

        $employee = Employee::where('registration_number', 'ENG-999')->firstOrFail();
        $this->assertNotNull($employee->profile_photo_path);
        $this->assertStringEndsWith('.webp', $employee->profile_photo_path);
        $this->assertNotNull($employee->photo_hash);
        $this->assertTrue(Storage::disk('public')->exists($employee->profile_photo_path));
    }

    /**
     * 8. Authorized user can update employee.
     */
    public function test_authorized_user_can_update_employee(): void
    {
        $this->standardUser->givePermissionTo(['view employees', 'edit employees']);

        $employee = Employee::factory()->create([
            'full_name' => 'Old Name',
            'position' => EmployeePosition::MeteringTechnician,
            'status' => EmployeeStatus::Active,
        ]);

        $response = $this->actingAs($this->standardUser)->put(route('master-data.employees.update', $employee), [
            'full_name' => 'Updated Name',
            'registration_number' => $employee->registration_number,
            'position' => EmployeePosition::SeniorMeteringEngineer->value,
            'status' => EmployeeStatus::OnLeave->value,
            'join_date' => '2026-01-15',
            'salary' => 95000.00,
            'daily_rate' => 11000.00,
        ]);

        $response->assertRedirect(route('master-data.employees'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'full_name' => 'Updated Name',
            'position' => EmployeePosition::SeniorMeteringEngineer->value,
            'status' => EmployeeStatus::OnLeave->value,
        ]);
    }

    /**
     * 9. Authorized user can soft delete employee.
     */
    public function test_authorized_user_can_soft_delete_employee(): void
    {
        $this->standardUser->givePermissionTo(['view employees', 'delete employees']);

        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->standardUser)->delete(route('master-data.employees.destroy', $employee));

        $response->assertRedirect(route('master-data.employees'));

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }

    /**
     * 10. Employee creation validates unique registration number.
     */
    public function test_employee_creation_validates_unique_registration_number(): void
    {
        $this->standardUser->givePermissionTo(['view employees', 'create employees']);

        Employee::factory()->create([
            'registration_number' => 'UNIQUE-001',
        ]);

        $response = $this->actingAs($this->standardUser)->post(route('master-data.employees.store'), [
            'full_name' => 'Duplicate Attempt',
            'registration_number' => 'UNIQUE-001',
            'position' => EmployeePosition::MeteringTechnician->value,
            'join_date' => '2026-09-01',
        ]);

        $response->assertSessionHasErrors('registration_number');
    }

    /**
     * 11. Filtering by position and status.
     */
    public function test_filtering_by_position_and_status(): void
    {
        $this->standardUser->givePermissionTo('view employees');

        Employee::factory()->create([
            'full_name' => 'Active Engineer',
            'position' => EmployeePosition::MeteringEngineer,
            'status' => EmployeeStatus::Active,
        ]);

        Employee::factory()->create([
            'full_name' => 'Inactive Technician',
            'position' => EmployeePosition::InstrumentationTechnician,
            'status' => EmployeeStatus::Inactive,
        ]);

        $response = $this->actingAs($this->standardUser)->get(route('master-data.employees', [
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
        ]));

        $response->assertOk();
        $response->assertSee('Active Engineer');
        $response->assertDontSee('Inactive Technician');
    }

    /**
     * 12. Authorized user can create and update employee with linked user.
     */
    public function test_authorized_user_can_create_and_update_employee_with_linked_user(): void
    {
        $this->standardUser->givePermissionTo(['view employees', 'create employees', 'edit employees']);

        $linkedUser = User::factory()->create([
            'profile_photo_path' => 'photos/user_avatar.webp',
            'photo_hash' => 'hash123',
        ]);

        // Create with linked user
        $response = $this->actingAs($this->standardUser)->post(route('master-data.employees.store'), [
            'full_name' => 'Linked Staff',
            'registration_number' => 'LINKED-001',
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
            'join_date' => '2026-09-01',
            'user_id' => $linkedUser->id,
        ]);

        $response->assertRedirect(route('master-data.employees'));

        $employee = Employee::where('registration_number', 'LINKED-001')->firstOrFail();
        $this->assertEquals($linkedUser->id, $employee->user_id);
        $this->assertEquals('photos/user_avatar.webp', $employee->profile_photo_path);

        // Update to unbind user
        $updateResponse = $this->actingAs($this->standardUser)->put(route('master-data.employees.update', $employee), [
            'full_name' => 'Linked Staff Unbound',
            'registration_number' => 'LINKED-001',
            'position' => EmployeePosition::MeteringEngineer->value,
            'status' => EmployeeStatus::Active->value,
            'join_date' => '2026-09-01',
            'user_id' => null,
        ]);

        $updateResponse->assertRedirect(route('master-data.employees'));

        $employee->refresh();
        $this->assertNull($employee->user_id);
        $this->assertEquals('Linked Staff Unbound', $employee->full_name);
    }

    /**
     * 13. Employees list renders unified position and status badges according to design tokens.
     */
    public function test_employees_page_renders_unified_position_and_status_badges(): void
    {
        $this->standardUser->givePermissionTo('view employees');

        $gm = Employee::factory()->create([
            'full_name' => 'General Director',
            'position' => EmployeePosition::GeneralManager,
            'status' => EmployeeStatus::Active,
        ]);

        $engineer = Employee::factory()->create([
            'full_name' => 'Field Engineer',
            'position' => EmployeePosition::SeniorMeteringEngineer,
            'status' => EmployeeStatus::OnLeave,
        ]);

        $tech = Employee::factory()->create([
            'full_name' => 'Field Technician',
            'position' => EmployeePosition::MeteringTechnician,
            'status' => EmployeeStatus::Inactive,
        ]);

        $this->assertEquals('primary', EmployeePosition::GeneralManager->badgeVariant());
        $this->assertEquals('info', EmployeePosition::SeniorMeteringEngineer->badgeVariant());
        $this->assertEquals('neutral', EmployeePosition::MeteringTechnician->badgeVariant());

        $this->assertEquals('success', EmployeeStatus::Active->badgeVariant());
        $this->assertEquals('warning', EmployeeStatus::OnLeave->badgeVariant());
        $this->assertEquals('danger', EmployeeStatus::Inactive->badgeVariant());

        $response = $this->actingAs($this->standardUser)->get(route('master-data.employees'));

        $response->assertOk();
        $response->assertSee($gm->position->label());
        $response->assertSee($engineer->position->label());
        $response->assertSee($tech->position->label());
        $response->assertSee($gm->status->label());
    }
}
