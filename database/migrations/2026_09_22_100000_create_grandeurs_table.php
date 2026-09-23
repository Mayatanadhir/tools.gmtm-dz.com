<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grandeurs', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->comment('Quantity parameter name e.g. Pressure, Temperature');
            $table->string('symbol', 50)->comment('Unit symbol e.g. Bar, °C, mA');
            $table->enum('type', ['measurement', 'source'])->default('measurement')->comment('Role: measurement or source generation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grandeurs');
    }
};
