<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->string('internal_code', 100)->nullable()->index()->comment('Storage / internal inventory code');
            $table->string('full_name', 255)->comment('Full name of the equipment');
            $table->string('short_name', 100)->nullable()->comment('Short acronym / display name');
            $table->string('serial_number', 150)->nullable()->unique()->comment('Manufacturer serial number');
            $table->string('category', 50)->default('measuring_instrument')->index()->comment('Equipment category (MeasuringInstrument, WorkTool, Vehicle, Other)');
            $table->string('package', 50)->default('none')->index()->comment('Assigned logistics package or lot');
            $table->boolean('requires_calibration')->default(false)->index()->comment('Whether periodic calibration is required');
            $table->string('designation', 255)->nullable()->comment('Detailed technical designation or specs');
            $table->string('status', 50)->default('active')->index()->comment('Operational status (active, maintenance, deployed, retired, inactive)');
            $table->string('image_path', 255)->nullable()->comment('Optimized WebP image filename in storage');
            $table->string('image_hash', 64)->nullable()->index()->comment('CAS SHA-256 hash for image deduplication');
            $table->string('certificate_path', 255)->nullable()->comment('Latest certificate filename in storage');
            $table->text('notes')->nullable()->comment('Administrative or maintenance notes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
