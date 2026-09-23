<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_specifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('grandeur_id')->constrained('grandeurs')->cascadeOnDelete();
            $table->double('range_min')->default(0)->comment('Minimum measurable or generated range value');
            $table->double('range_max')->default(0)->comment('Maximum measurable or generated range value');
            $table->double('accuracy_value')->default(0)->comment('Accuracy value margin');
            $table->string('accuracy_type', 20)->default('%')->comment('Accuracy type (% or abs)');
            $table->timestamps();

            $table->index(['equipment_id', 'grandeur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_specifications');
    }
};
