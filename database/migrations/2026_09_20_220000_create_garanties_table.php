<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garanties', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique()->comment('Guarantee reference number');
            $table->string('bank_name')->comment('Issuing bank name');
            $table->decimal('amount', 15, 2)->default(0)->comment('Guarantee amount (DZD)');
            $table->date('started_at')->nullable()->comment('Guarantee start date');
            $table->enum('status', ['active', 'expired', 'released', 'cancelled'])->default('active')->comment('Current status of the guarantee');
            $table->enum('type', ['bid_bond', 'performance', 'advance_payment', 'retention', 'other'])->default('other')->comment('Type of bank guarantee');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garanties');
    }
};
