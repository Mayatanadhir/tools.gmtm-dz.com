<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibration_points', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('calibration_certificate_id')->constrained('calibration_certificates')->cascadeOnDelete();
            $table->foreignId('equipment_specification_id')->nullable()->constrained('equipment_specifications')->nullOnDelete();
            $table->double('nominal_value')->comment('Nominal or reference standard setpoint');
            $table->double('correction')->default(0)->comment('Calculated correction value');
            $table->double('uncertainty')->default(0)->comment('Expanded measurement uncertainty');
            $table->string('status', 50)->default('in_tolerance')->index()->comment('Tolerance conformity status');
            $table->timestamps();

            $table->index(['calibration_certificate_id', 'equipment_specification_id'], 'cal_points_cert_spec_idx');
            $table->index(['calibration_certificate_id', 'nominal_value'], 'cal_points_cert_nom_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibration_points');
    }
};
