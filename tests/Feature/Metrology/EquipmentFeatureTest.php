<?php

declare(strict_types=1);

namespace Tests\Feature\Metrology;

use App\Enums\EquipmentCategory;
use App\Enums\EquipmentPackage;
use App\Enums\EquipmentStatus;
use App\Enums\GrandeurType;
use App\Models\Equipment;
use App\Models\Grandeur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EquipmentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Grandeur $grandeurPressure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('permissions:sync-tables');

        $superRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole($superRole);

        $this->grandeurPressure = Grandeur::create([
            'name' => 'Pression',
            'symbol' => 'bar',
            'type' => GrandeurType::Measurement,
        ]);
    }

    public function test_can_view_equipment_index_page_with_kpis(): void
    {
        Equipment::create([
            'full_name' => 'Digital Manometer Test',
            'short_name' => 'DMT-01',
            'internal_code' => 'DMT-2026',
            'category' => EquipmentCategory::MeasuringInstrument,
            'package' => EquipmentPackage::Lot01,
            'status' => EquipmentStatus::Active,
            'requires_calibration' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('metrology.equipment'));

        $response->assertOk();
        $response->assertDontSee('@js(');
        $response->assertSee('openEditModal(JSON.parse(atob($el.dataset.item))');
        $response->assertSee('data-item=');
        $response->assertSee('Digital Manometer Test');
        $response->assertSee('DMT-01');
        $response->assertSee('DMT-2026');
    }

    public function test_can_view_equipment_show_details_page(): void
    {
        $equipment = Equipment::create([
            'full_name' => 'Fluke Multimeter',
            'short_name' => 'FLK-87V',
            'internal_code' => 'EQ-FLK-001',
            'category' => EquipmentCategory::MeasuringInstrument,
            'package' => EquipmentPackage::Lot01,
            'status' => EquipmentStatus::Active,
            'requires_calibration' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('metrology.equipment.show', $equipment));

        $response->assertOk();
        $response->assertSee('Fluke Multimeter');
        $response->assertSee('EQ-FLK-001');
    }

    public function test_can_create_equipment_with_specifications(): void
    {
        $data = [
            'full_name' => 'Druck DPI 610 Calibrator',
            'short_name' => 'DPI-610',
            'internal_code' => 'EQ-CAL-099',
            'serial_number' => 'SN-998877',
            'category' => EquipmentCategory::MeasuringInstrument->value,
            'package' => EquipmentPackage::Lot01->value,
            'status' => EquipmentStatus::Active->value,
            'requires_calibration' => '1',
            'params' => [
                $this->grandeurPressure->id => [
                    'selected' => '1',
                    'min' => '0.0',
                    'max' => '20.0',
                    'acc' => '0.025',
                    'acc_type' => '%',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('metrology.equipment.store'), $data);

        $response->assertRedirect(route('metrology.equipment'));
        $this->assertDatabaseHas('equipment', [
            'internal_code' => 'EQ-CAL-099',
            'full_name' => 'Druck DPI 610 Calibrator',
        ]);

        $created = Equipment::where('internal_code', 'EQ-CAL-099')->firstOrFail();
        $this->assertCount(1, $created->specifications);
        $this->assertEquals(20.0, $created->specifications->first()->range_max);
    }

    public function test_can_update_equipment_and_sync_specifications(): void
    {
        $equipment = Equipment::create([
            'full_name' => 'Original Gauge',
            'short_name' => 'OG-01',
            'internal_code' => 'EQ-OG-01',
            'category' => EquipmentCategory::WorkTool,
            'package' => EquipmentPackage::None,
            'status' => EquipmentStatus::Active,
            'requires_calibration' => false,
        ]);

        $updateData = [
            'full_name' => 'Updated Precision Gauge',
            'short_name' => 'UPG-01',
            'internal_code' => 'EQ-OG-01',
            'category' => EquipmentCategory::MeasuringInstrument->value,
            'package' => EquipmentPackage::Lot02->value,
            'status' => EquipmentStatus::Maintenance->value,
            'requires_calibration' => '1',
            'params' => [
                $this->grandeurPressure->id => [
                    'selected' => '1',
                    'min' => '-1.0',
                    'max' => '30.0',
                    'acc' => '0.05',
                    'acc_type' => 'abs',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('metrology.equipment.update', $equipment), $updateData);

        $response->assertRedirect(route('metrology.equipment'));
        $equipment->refresh();

        $this->assertSame('Updated Precision Gauge', $equipment->full_name);
        $this->assertSame(EquipmentStatus::Maintenance, $equipment->status);
        $this->assertCount(1, $equipment->specifications);
        $this->assertEquals(-1.0, $equipment->specifications->first()->range_min);
    }

    public function test_can_soft_delete_equipment(): void
    {
        $equipment = Equipment::create([
            'full_name' => 'Equipment To Delete',
            'internal_code' => 'EQ-DEL-001',
            'category' => EquipmentCategory::Other,
            'package' => EquipmentPackage::None,
            'status' => EquipmentStatus::Inactive,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('metrology.equipment.destroy', $equipment));

        $response->assertRedirect(route('metrology.equipment'));
        $this->assertSoftDeleted('equipment', ['id' => $equipment->id]);
    }
}
