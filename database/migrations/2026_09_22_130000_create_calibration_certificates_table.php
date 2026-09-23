<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibration_certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 100)->nullable()->index()->comment('Official certificate reference / number');
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->string('laboratory_name', 255)->nullable()->comment('Accredited laboratory name');
            $table->date('calibration_date')->nullable()->index()->comment('Calibration execution date');
            $table->decimal('price', 15, 2)->default(0)->comment('Calibration service cost');
            $table->boolean('is_locked')->default(false)->comment('Locked status after audit validation');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['equipment_id', 'calibration_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibration_certificates');
    }
};
