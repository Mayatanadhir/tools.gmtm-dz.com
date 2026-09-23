<?php

declare(strict_types=1);

namespace Tests\Feature\Metrology;

use App\Enums\CalibrationCertificateStatus;
use App\Enums\CalibrationPointStatus;
use App\Enums\EquipmentCategory;
use App\Enums\EquipmentPackage;
use App\Enums\EquipmentStatus;
use App\Models\CalibrationCertificate;
use App\Models\CalibrationPoint;
use App\Models\Equipment;
use App\Models\User;
use App\Services\CalibrationCertificateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalibrationCertificateWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('permissions:sync-tables');

        $superRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole($superRole);

        $this->equipment = Equipment::create([
            'full_name' => 'Pressure Calibrator Additel ADT672',
            'short_name' => 'ADT672 100 bar',
            'internal_code' => 'LAB-WC-002',
            'serial_number' => 'SN-ADT672-007',
            'category' => EquipmentCategory::MeasuringInstrument,
            'package' => EquipmentPackage::Lot01,
            'status' => EquipmentStatus::Active,
            'requires_calibration' => true,
        ]);
    }

    public function test_authenticated_user_can_view_certificates_index_with_statistics(): void
    {
        CalibrationCertificate::create([
            'reference' => 'CERT-2025-001',
            'certificate_type' => 'periodic',
            'equipment_id' => $this->equipment->id,
            'laboratory_name' => 'RE.EL SERVICES',
            'calibration_date' => Carbon::now()->subMonths(6),
            'expiry_date' => Carbon::now()->addMonths(6),
            'validity_period_months' => 12,
            'price' => 5000.0,
            'status' => CalibrationCertificateStatus::Approved,
            'is_locked' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('metrology.calibration-certificates'));

        $response->assertOk()
            ->assertViewIs('metrology.certificates.index')
            ->assertSee('CERT-2025-001')
            ->assertSee('ADT672 100 bar')
            ->assertSee('RE.EL SERVICES');
    }

    public function test_user_can_filter_certificates_by_equipment_and_status(): void
    {
        $cert1 = CalibrationCertificate::create([
            'reference' => 'CERT-MATCH',
            'equipment_id' => $this->equipment->id,
            'calibration_date' => Carbon::now(),
            'status' => CalibrationCertificateStatus::Approved,
        ]);

        $otherEquipment = Equipment::create([
            'full_name' => 'Other Equipment',
            'short_name' => 'Other',
            'internal_code' => 'OTH-001',
            'category' => EquipmentCategory::MeasuringInstrument,
            'package' => EquipmentPackage::Lot01,
            'status' => EquipmentStatus::Active,
            'requires_calibration' => true,
        ]);

        $cert2 = CalibrationCertificate::create([
            'reference' => 'CERT-DIFFERENT',
            'equipment_id' => $otherEquipment->id,
            'calibration_date' => Carbon::now(),
            'status' => CalibrationCertificateStatus::Draft,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('metrology.calibration-certificates', ['equipment_id' => $this->equipment->id]));

        $response->assertOk()
            ->assertSee('CERT-MATCH')
            ->assertDontSee('CERT-DIFFERENT');
    }

    public function test_user_can_create_certificate_with_points_and_pdf_document(): void
    {
        Storage::fake('public');

        $pdfFile = UploadedFile::fake()->create('official_calibration_cert.pdf', 500, 'application/pdf');

        $payload = [
            'reference' => 'CERT-NEW-2026',
            'certificate_type' => 'periodic',
            'equipment_id' => $this->equipment->id,
            'laboratory_name' => 'ONML Metrology Lab',
            'calibration_date' => '2026-09-01',
            'validity_period_months' => 12,
            'price' => 7500.50,
            'certificate_file' => $pdfFile,
            'environmental_conditions' => [
                'temperature_celsius' => 21.5,
                'humidity_percent' => 48.0,
                'atmospheric_pressure_hpa' => 1012.0,
            ],
            'remarks' => 'Calibrated in temperature controlled bath.',
            'points' => [
                ['nominal_value' => 0.0, 'correction' => 0.0, 'uncertainty' => 0.005],
                ['nominal_value' => 20.0, 'correction' => -0.01, 'uncertainty' => 0.008],
                ['nominal_value' => 50.0, 'correction' => 0.02, 'uncertainty' => 0.012],
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('metrology.calibration-certificates.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('calibration_certificates', [
            'reference' => 'CERT-NEW-2026',
            'equipment_id' => $this->equipment->id,
            'laboratory_name' => 'ONML Metrology Lab',
            'price' => 7500.50,
            'status' => CalibrationCertificateStatus::Draft->value,
            'is_locked' => 0,
        ]);

        $cert = CalibrationCertificate::where('reference', 'CERT-NEW-2026')->firstOrFail();
        $this->assertNotNull($cert->certificate_path);
        $this->assertNotNull($cert->certificate_hash);
        $this->assertEquals(3, $cert->calibrationPoints()->count());

        // Ensure equipment shortcut was updated
        $this->equipment->refresh();
        $this->assertEquals($cert->certificate_path, $this->equipment->certificate_path);
    }

    public function test_user_can_view_certificate_details_and_curves(): void
    {
        $cert = CalibrationCertificate::create([
            'reference' => 'CERT-SHOW-TEST',
            'equipment_id' => $this->equipment->id,
            'laboratory_name' => 'CETIM',
            'calibration_date' => '2026-05-10',
            'expiry_date' => '2027-05-10',
            'price' => 4500.0,
            'status' => CalibrationCertificateStatus::Approved,
            'is_locked' => true,
        ]);

        CalibrationPoint::create([
            'calibration_certificate_id' => $cert->id,
            'nominal_value' => 25.0,
            'correction' => -0.05,
            'uncertainty' => 0.015,
            'status' => CalibrationPointStatus::InTolerance,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('metrology.calibration-certificates.show', $cert));

        $response->assertOk()
            ->assertViewIs('metrology.certificates.show')
            ->assertSee('CERT-SHOW-TEST')
            ->assertSee('CETIM')
            ->assertSee('25')
            ->assertSee('-0.05');
    }

    public function test_authorized_user_can_approve_and_lock_certificate(): void
    {
        $cert = CalibrationCertificate::create([
            'reference' => 'CERT-DRAFT-LOCK',
            'equipment_id' => $this->equipment->id,
            'calibration_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addYear(),
            'status' => CalibrationCertificateStatus::Draft,
            'is_locked' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('metrology.calibration-certificates.approve', $cert));

        $response->assertRedirect();

        $cert->refresh();
        $this->assertTrue($cert->is_locked);
        $this->assertEquals(CalibrationCertificateStatus::Approved, $cert->status);
        $this->assertEquals($this->adminUser->id, $cert->approved_by);
        $this->assertEquals($this->adminUser->id, $cert->locked_by);
    }

    public function test_locked_certificate_cannot_be_modified_or_deleted(): void
    {
        $cert = CalibrationCertificate::create([
            'reference' => 'CERT-IMMUTABLE',
            'equipment_id' => $this->equipment->id,
            'calibration_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addYear(),
            'status' => CalibrationCertificateStatus::Approved,
            'is_locked' => true,
        ]);

        // Attempt edit view
        $editResponse = $this->actingAs($this->adminUser)
            ->get(route('metrology.calibration-certificates.edit', $cert));
        $editResponse->assertRedirect(route('metrology.calibration-certificates.show', $cert));

        // Attempt destroy
        $this->expectException(\RuntimeException::class);
        $this->app->make(CalibrationCertificateService::class)->deleteCertificate($cert);
    }

    public function test_authorized_user_can_unlock_certificate_with_audit_reason(): void
    {
        $cert = CalibrationCertificate::create([
            'reference' => 'CERT-LOCKED-FOR-UNLOCK',
            'equipment_id' => $this->equipment->id,
            'calibration_date' => Carbon::now(),
            'expiry_date' => Carbon::now()->addYear(),
            'status' => CalibrationCertificateStatus::Approved,
            'is_locked' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('metrology.calibration-certificates.unlock', $cert), [
                'reason' => 'Auditor requested correction in laboratory certificate reference number.',
            ]);

        $response->assertRedirect();

        $cert->refresh();
        $this->assertFalse($cert->is_locked);
        $this->assertEquals(CalibrationCertificateStatus::UnderReview, $cert->status);
    }

    public function test_check_expiring_certificates_command_transitions_status(): void
    {
        // 1. Expired certificate (expiry date was 10 days ago)
        $expiredCert = CalibrationCertificate::create([
            'reference' => 'CERT-PAST-DUE',
            'equipment_id' => $this->equipment->id,
            'calibration_date' => Carbon::now()->subYear()->subDays(10),
            'expiry_date' => Carbon::now()->subDays(10),
            'status' => CalibrationCertificateStatus::Approved,
            'is_locked' => true,
        ]);

        // 2. Expiring soon certificate (expiry in 15 days)
        $expiringCert = CalibrationCertificate::create([
            'reference' => 'CERT-SOON',
            'equipment_id' => $this->equipment->id,
            'calibration_date' => Carbon::now()->subYear()->addDays(15),
            'expiry_date' => Carbon::now()->addDays(15),
            'status' => CalibrationCertificateStatus::Approved,
            'is_locked' => true,
        ]);

        $this->artisan('metrology:check-expiring-certificates', ['--days' => 30])
            ->assertSuccessful();

        $expiredCert->refresh();
        $expiringCert->refresh();

        $this->assertEquals(CalibrationCertificateStatus::Expired, $expiredCert->status);
        $this->assertEquals(CalibrationCertificateStatus::ExpiringSoon, $expiringCert->status);
    }
}
