<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AccountStatus;
use App\Enums\EmployeePosition;
use App\Enums\EmployeeStatus;
use PHPUnit\Framework\TestCase;

class EnumsBadgeContractTest extends TestCase
{
    /**
     * Verify AccountStatus implements the unified badgeVariant contract.
     */
    public function test_account_status_badge_variants(): void
    {
        $this->assertSame('success', AccountStatus::Active->badgeVariant());
        $this->assertSame('danger', AccountStatus::Suspended->badgeVariant());

        // Verify legacy badgeClass produces modern alpha tokens
        $this->assertStringContainsString('bg-emerald-500/10', AccountStatus::Active->badgeClass());
        $this->assertStringContainsString('bg-rose-500/10', AccountStatus::Suspended->badgeClass());
    }

    /**
     * Verify EmployeeStatus implements the unified badgeVariant contract.
     */
    public function test_employee_status_badge_variants(): void
    {
        $this->assertSame('success', EmployeeStatus::Active->badgeVariant());
        $this->assertSame('danger', EmployeeStatus::Inactive->badgeVariant());
        $this->assertSame('warning', EmployeeStatus::OnLeave->badgeVariant());
    }

    /**
     * Verify EmployeePosition implements the functional hierarchy classification.
     */
    public function test_employee_position_functional_hierarchy_classification(): void
    {
        // Management & Executive Leadership -> primary (brand)
        $this->assertSame('primary', EmployeePosition::GeneralManager->badgeVariant());

        // Engineering & Specialist Roles -> info (indigo)
        $this->assertSame('info', EmployeePosition::SeniorMeteringEngineer->badgeVariant());
        $this->assertSame('info', EmployeePosition::MeteringEngineer->badgeVariant());
        $this->assertSame('info', EmployeePosition::SeniorInstrumentationEngineer->badgeVariant());

        // Field Operations & Technicians -> neutral (gray)
        $this->assertSame('neutral', EmployeePosition::MeteringTechnician->badgeVariant());
        $this->assertSame('neutral', EmployeePosition::InstrumentationTechnician->badgeVariant());
    }
}
