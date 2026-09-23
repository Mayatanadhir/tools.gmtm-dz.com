<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name', 150)->index('employees_full_name_index');
            $table->string('registration_number', 50)->unique('employees_registration_number_unique');
            $table->string('position', 50);
            $table->string('status', 20)->default('active')->index('employees_status_index');
            $table->date('join_date');
            $table->decimal('salary', 12, 2)->default(0.00);
            $table->decimal('daily_rate', 10, 2)->default(0.00);
            $table->string('address', 255)->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('photo_hash', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
